<?php

namespace App\Services;

use App\Exceptions\ExcelImport\EmptyExcelFileException;
use App\Exceptions\ExcelImport\ImportAlreadyConfirmedException;
use App\Models\AdminUser;
use App\Models\ExcelImport;
use App\Models\ExcelImportRow;
use App\Models\GameType;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Skill;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImportService
{
    // D6 — قراءة الملف + مطابقة أعمدة تلقائية + تحقق لكل صف على حدة (بدون رفض الملف كله)
    // TODO: معالجة متزامنة ضمن نفس الطلب حاليًا (قرار مؤقت، انظر config/excel_import_rules.php)
    public function import(UploadedFile $file, AdminUser $admin): ExcelImport
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $allRows = $sheet->toArray(null, true, true, false);

        if (empty($allRows)) {
            throw new EmptyExcelFileException();
        }

        $header = array_shift($allRows);

        if (empty($allRows)) {
            throw new EmptyExcelFileException();
        }

        $dataRows = array_slice($allRows, 0, config('excel_import_rules.max_upload_rows'));
        $columnMap = $this->autoMapHeaders($header);

        $import = ExcelImport::create([
            'admin_user_id' => $admin->id,
            'file_name' => $file->getClientOriginalName(),
            'status' => 'processing',
            'total_rows' => count($dataRows),
            'success_rows' => 0,
            'error_rows' => 0,
        ]);

        $successCount = 0;
        $errorCount = 0;

        foreach ($dataRows as $index => $rowValues) {
            if ($this->isBlankRow($rowValues)) {
                continue; // صف فارغ بالكامل، لا يُحتسب بالإجمالي أصلًا
            }

            $rawData = $this->buildRawData($header, $rowValues);
            $mappedInput = $this->applyMapping($columnMap, $rowValues);

            [$isValid, $errorMessage, $resolved] = $this->validateAndResolve($mappedInput);

            ExcelImportRow::create([
                'import_id' => $import->id,
                'row_number' => $index + 2, // +1 فهرسة من 1، +1 لصف العناوين
                'raw_data' => $rawData,
                'mapped_data' => $resolved,
                'status' => $isValid ? 'valid' : 'error',
                'error_message' => $errorMessage,
            ]);

            $isValid ? $successCount++ : $errorCount++;
        }

        $import->update(['success_rows' => $successCount, 'error_rows' => $errorCount]);

        return $import->fresh();
    }

    // تصحيح يدوي لعمود لم تنجح مطابقته تلقائيًا (أو تعديل أي حقل بالصف)، مع إعادة التحقق فورًا
    public function updateRowMapping(ExcelImportRow $row, array $corrections): ExcelImportRow
    {
        if ($row->import->status === 'completed') {
            throw new ImportAlreadyConfirmedException();
        }

        $merged = array_merge($row->mapped_data ?? [], $corrections);
        [$isValid, $errorMessage, $resolved] = $this->validateAndResolve($merged);

        $row->update([
            'mapped_data' => $resolved,
            'status' => $isValid ? 'valid' : 'error',
            'error_message' => $errorMessage,
        ]);

        $import = $row->import;
        $import->update([
            'success_rows' => ExcelImportRow::where('import_id', $import->id)->where('status', 'valid')->count(),
            'error_rows' => ExcelImportRow::where('import_id', $import->id)->where('status', 'error')->count(),
        ]);

        return $row->fresh();
    }

    // النشر الفعلي: يُنشئ Question حقيقية لكل صف valid فقط، وفقط عند هذا التأكيد الصريح
    public function confirm(ExcelImport $import): ExcelImport
    {
        if ($import->status === 'completed') {
            throw new ImportAlreadyConfirmedException();
        }

        return DB::transaction(function () use ($import) {
            $validRows = ExcelImportRow::where('import_id', $import->id)->where('status', 'valid')->get();

            foreach ($validRows as $row) {
                $data = $row->mapped_data;
                $gameType = GameType::where('code', $data['game_type'])->first();

                $question = Question::create([
                    'lesson_id' => $data['lesson_id'],
                    'skill_id' => $data['skill_id'],
                    'game_type_id' => $gameType->id,
                    'question_text' => $data['question_text'],
                    'difficulty' => $data['difficulty'],
                    'payload' => $data['payload'],
                    'explanation' => $data['explanation'],
                    // تُنشر لاحقًا يدويًا عبر D4/D5 (خارج نطاق هذه المرحلة) — الاستيراد لا ينشر تلقائيًا
                    'status' => 'draft',
                    'source' => 'import',
                    'created_by' => $import->admin_user_id,
                ]);

                $row->update(['status' => 'imported', 'created_question_id' => $question->id]);
            }

            // الصفوف الخاطئة المتبقية (error) تبقى بحالتها كتوثيق أنها لم تُستورد أبدًا
            $import->update(['status' => 'completed']);

            return $import->fresh();
        });
    }

    private function isBlankRow(array $rowValues): bool
    {
        foreach ($rowValues as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function buildRawData(array $header, array $rowValues): array
    {
        $raw = [];
        foreach ($header as $i => $label) {
            $key = ($label !== null && trim((string) $label) !== '') ? (string) $label : "col_{$i}";
            $raw[$key] = $rowValues[$i] ?? null;
        }

        return $raw;
    }

    private function autoMapHeaders(array $header): array
    {
        $map = [];

        foreach (config('excel_import_rules.expected_columns') as $field) {
            foreach ($header as $index => $label) {
                if ($label !== null && strcasecmp(trim((string) $label), $field) === 0) {
                    $map[$field] = $index;
                    break;
                }
            }
        }

        return $map;
    }

    private function applyMapping(array $columnMap, array $rowValues): array
    {
        $mapped = [];

        foreach (config('excel_import_rules.expected_columns') as $field) {
            $mapped[$field] = isset($columnMap[$field]) ? ($rowValues[$columnMap[$field]] ?? null) : null;
        }

        return $mapped;
    }

    /**
     * @return array{0: bool, 1: ?string, 2: array} [صالح؟, رسالة الخطأ المجمَّعة أو null, mapped_data النهائي]
     */
    private function validateAndResolve(array $mapped): array
    {
        $errors = [];
        $resolved = $mapped;

        $lessonId = is_numeric($mapped['lesson_id'] ?? null) ? (int) $mapped['lesson_id'] : null;
        if (! $lessonId || ! Lesson::whereKey($lessonId)->exists()) {
            $errors[] = 'lesson_id غير موجود أو غير رقمي';
        }
        $resolved['lesson_id'] = $lessonId;

        $gameTypeCode = is_string($mapped['game_type'] ?? null) ? strtolower(trim($mapped['game_type'])) : null;
        $supportedTypes = config('excel_import_rules.supported_game_types');
        if (! in_array($gameTypeCode, $supportedTypes, true)) {
            $errors[] = 'game_type يجب أن يكون واحدًا من: '.implode('، ', $supportedTypes);
        } elseif (! GameType::where('code', $gameTypeCode)->exists()) {
            // النمط مدعوم بالتصحيح (config) لكن لا يوجد له صف فعلي بجدول game_types بعد
            $errors[] = "game_type '{$gameTypeCode}' غير موجود فعليًا بجدول game_types";
        }
        $resolved['game_type'] = $gameTypeCode;

        $questionText = trim((string) ($mapped['question_text'] ?? ''));
        if ($questionText === '') {
            $errors[] = 'question_text فارغ';
        }
        $resolved['question_text'] = $questionText;

        $difficultyRaw = trim((string) ($mapped['difficulty'] ?? ''));
        $difficulty = $difficultyRaw !== '' ? strtolower($difficultyRaw) : 'medium';
        if (! in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
            $errors[] = 'difficulty يجب أن يكون easy أو medium أو hard';
        }
        $resolved['difficulty'] = $difficulty;

        // إنشاء Skill تلقائيًا إن ذُكر اسم غير موجود، بدل رفض الصف
        $skillName = trim((string) ($mapped['skill_name'] ?? ''));
        $resolved['skill_id'] = null;
        if ($skillName !== '') {
            $resolved['skill_id'] = Skill::firstOrCreate(['name' => $skillName])->id;
        }

        $payload = null;
        if ($gameTypeCode === 'mcq') {
            $options = [];
            foreach (['a', 'b', 'c', 'd'] as $letter) {
                $text = trim((string) ($mapped["option_{$letter}"] ?? ''));
                if ($text !== '') {
                    $options[] = ['id' => $letter, 'text' => $text];
                }
            }

            $correctOption = is_string($mapped['correct_option'] ?? null) ? strtolower(trim($mapped['correct_option'])) : null;

            if (count($options) < 2) {
                $errors[] = 'نمط mcq يتطلب عمودين على الأقل من option_a..option_d بقيمة غير فارغة';
            } elseif (! $correctOption || ! in_array($correctOption, array_column($options, 'id'), true)) {
                $errors[] = 'correct_option يجب أن يطابق أحد حروف الخيارات المُدخَلة فعليًا (a/b/c/d)';
            } else {
                $payload = ['options' => $options, 'correct_option_id' => $correctOption];
            }
        } elseif ($gameTypeCode === 'true_false') {
            $rawAnswer = is_string($mapped['correct_answer'] ?? null) ? strtolower(trim($mapped['correct_answer'])) : null;
            $boolAnswer = match ($rawAnswer) {
                'true', '1', 'صح', 'صحيح' => true,
                'false', '0', 'خطأ', 'خاطئ' => false,
                default => null,
            };

            if ($boolAnswer === null) {
                $errors[] = 'نمط true_false يتطلب عمود correct_answer بقيمة true/false أو صح/خطأ';
            } else {
                $payload = ['correct_answer' => $boolAnswer];
            }
        }
        $resolved['payload'] = $payload;

        $explanation = trim((string) ($mapped['explanation'] ?? ''));
        $resolved['explanation'] = $explanation !== '' ? $explanation : null;

        return [empty($errors), empty($errors) ? null : implode(' | ', $errors), $resolved];
    }
}
