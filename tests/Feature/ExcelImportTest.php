<?php

namespace Tests\Feature;

use App\Models\AdminRole;
use App\Models\AdminUser;
use App\Models\Grade;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Skill;
use App\Models\Subject;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdminToken(): string
    {
        $role = AdminRole::create(['code' => 'super_admin', 'name_ar' => 'مشرف', 'name_en' => 'Admin']);
        $admin = AdminUser::create([
            'full_name' => 'مشرف', 'email' => 'admin@example.com',
            'password_hash' => Hash::make('AdminPass123'), 'role_id' => $role->id, 'status' => 'active',
        ]);

        return $admin->createToken('t')->plainTextToken;
    }

    private function buildXlsxUpload(int $lessonId): UploadedFile
    {
        $header = ['lesson_id', 'game_type', 'question_text', 'difficulty', 'skill_name', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_option', 'correct_answer', 'explanation'];

        $rows = [
            // صف صالح: mcq
            [$lessonId, 'mcq', 'ما ناتج 2+2؟', 'easy', 'جمع', '3', '4', '5', '', 'b', '', 'لأن 2+2=4'],
            // صف صالح: true_false
            [$lessonId, 'true_false', 'الأرض كروية', 'medium', '', '', '', '', '', '', 'true', ''],
            // صف خاطئ: lesson_id غير موجود
            [999999, 'mcq', 'سؤال بدرس غير موجود', 'easy', '', '1', '2', '', '', 'a', '', ''],
            // صف خاطئ: question_text فارغ
            [$lessonId, 'mcq', '', 'easy', '', '1', '2', '', '', 'a', '', ''],
            // صف خاطئ: mcq بدون correct_option صالح
            [$lessonId, 'mcq', 'سؤال بلا إجابة صحيحة محدَّدة', 'easy', '', '1', '2', '', '', 'z', '', ''],
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($header, null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        $path = tempnam(sys_get_temp_dir(), 'xlsx_test_').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'questions.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    public function test_upload_validates_rows_individually_then_confirm_publishes_only_valid_ones(): void
    {
        $token = $this->makeAdminToken();

        \App\Models\GameType::create(['code' => 'mcq', 'name_ar' => 'mcq', 'name_en' => 'mcq', 'is_active' => true]);
        \App\Models\GameType::create(['code' => 'true_false', 'name_ar' => 'true_false', 'name_en' => 'true_false', 'is_active' => true]);

        $grade = Grade::create(['level' => 1, 'name_ar' => 'الأول', 'name_en' => 'Grade 1']);
        $subject = Subject::create(['name_ar' => 'مادة', 'name_en' => 'Subject']);
        $unit = Unit::create(['subject_id' => $subject->id, 'grade_id' => $grade->id, 'title' => 'وحدة', 'sort_order' => 1, 'status' => 'published']);
        $lesson = Lesson::create(['unit_id' => $unit->id, 'title' => 'درس', 'sort_order' => 1, 'status' => 'published']);

        $file = $this->buildXlsxUpload($lesson->id);

        $uploadResp = $this->withToken($token)
            ->post('/api/admin/excel-imports/upload', ['file' => $file])
            ->assertStatus(201)
            ->json();

        $this->assertSame(5, $uploadResp['import']['total_rows']);
        $this->assertSame(2, $uploadResp['import']['success_rows']);
        $this->assertSame(3, $uploadResp['import']['error_rows']);
        $this->assertSame('processing', $uploadResp['import']['status']);

        // لا سؤال حقيقي يُنشأ قبل التأكيد الصريح
        $this->assertSame(0, Question::count());

        $rows = collect($uploadResp['rows']);
        $errorRows = $rows->where('status', 'error')->values();
        $this->assertCount(3, $errorRows);
        // كل صف خاطئ له رسالة خطأ مختلفة وواضحة (لا رسالة عامة موحّدة)
        $this->assertNotNull($errorRows[0]['error_message']);
        $this->assertStringContainsString('lesson_id', $errorRows[0]['error_message']);
        $this->assertStringContainsString('question_text', $errorRows[1]['error_message']);
        $this->assertStringContainsString('correct_option', $errorRows[2]['error_message']);

        $importId = $uploadResp['import']['id'];
        $badQuestionTextRowId = $errorRows[1]['id'];

        // تصحيح يدوي لصف "question_text فارغ" بإضافة النص الناقص
        $correctResp = $this->withToken($token)
            ->patchJson("/api/admin/excel-import-rows/{$badQuestionTextRowId}/mapping", [
                'mapped_data' => ['question_text' => 'سؤال بعد التصحيح اليدوي'],
            ])
            ->assertStatus(200)
            ->json();

        $this->assertSame('valid', $correctResp['status']);
        $this->assertNull($correctResp['error_message']);

        // التأكيد الفعلي: يُنشر الآن 3 أسئلة (2 الأصليتان + 1 المصحَّحة)
        $confirmResp = $this->withToken($token)
            ->postJson("/api/admin/excel-imports/{$importId}/confirm")
            ->assertStatus(200)
            ->json();

        $this->assertSame('completed', $confirmResp['status']);
        $this->assertSame(3, Question::count());

        $mcqQuestion = Question::where('question_text', 'ما ناتج 2+2؟')->first();
        $this->assertNotNull($mcqQuestion);
        $this->assertSame('draft', $mcqQuestion->status); // لا نشر تلقائي، فقط إنشاء بحالة draft
        $this->assertSame('import', $mcqQuestion->source);
        $this->assertSame('b', $mcqQuestion->payload['correct_option_id']);

        $tfQuestion = Question::where('question_text', 'الأرض كروية')->first();
        $this->assertTrue($tfQuestion->payload['correct_answer']);

        // Skill "جمع" أُنشئت تلقائيًا لأنها لم تكن موجودة
        $this->assertSame(1, Skill::where('name', 'جمع')->count());
        $this->assertSame($mcqQuestion->skill_id, Skill::where('name', 'جمع')->first()->id);

        // صفّان يبقيان بحالة error نهائيًا (lesson_id غير موجود، وcorrect_option غير صالح)
        $this->assertSame(2, \App\Models\ExcelImportRow::where('import_id', $importId)->where('status', 'error')->count());
        $this->assertSame(3, \App\Models\ExcelImportRow::where('import_id', $importId)->where('status', 'imported')->count());

        // لا يمكن التأكيد مرتين
        $this->withToken($token)
            ->postJson("/api/admin/excel-imports/{$importId}/confirm")
            ->assertStatus(422);

        // ولا تعديل أي صف بعد التأكيد
        $this->withToken($token)
            ->patchJson("/api/admin/excel-import-rows/{$badQuestionTextRowId}/mapping", ['mapped_data' => []])
            ->assertStatus(422);
    }

    public function test_parent_token_cannot_access_admin_excel_upload_route(): void
    {
        $parent = \App\Models\ParentUser::create([
            'public_id' => 'RQMP-000001', 'full_name' => 'ولي أمر', 'email' => 'parent@example.com',
            'password_hash' => Hash::make('Password123'), 'status' => 'active',
        ]);
        $token = $parent->createToken('t')->plainTextToken;

        $this->withToken($token)
            ->post('/api/admin/excel-imports/upload', [])
            ->assertStatus(401);
    }
}
