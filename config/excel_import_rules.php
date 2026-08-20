<?php

// TODO: قرار مؤقت يحتاج تأكيد فريق المحتوى — لا توجد وثيقة سابقة تحدد شكل أعمدة
// ملف Excel المتوقَّع لاستيراد الأسئلة، فهذا أول تعريف رسمي له بالمشروع. الأعمدة
// أدناه (بالاسم بالصف الأول من الملف، غير حساسة لحالة الأحرف) هي ما يحاول
// المطابقة التلقائية (auto column mapping) إيجاده؛ أي عمود لم يُعثر عليه تلقائيًا
// يمكن للإداري تصحيحه يدويًا عبر PATCH /admin/excel-import-rows/{row}/mapping
// قبل التأكيد النهائي.
//
// TODO: دعم أنماط الألعبة محصور بـ mcq وtrue_false فقط بهذه المرحلة (نفس نطاق
// AnswerGraderFactory بالمرحلة 3) — أي صف بنمط آخر يُرفض بخطأ واضح بدل تخمين
// بنية payload غير مدعومة أصلًا بمنطق التصحيح الحالي.
//
// TODO: المعالجة متزامنة (ضمن نفس الطلب) بهذه المرحلة لتوفير الوقت، وليست
// Queued Job كما تشترط الوثيقة الأصلية لملفات كبيرة الحجم. قرار مؤقت يحتاج
// تحويل صريح لـ Queue (مثلًا ProcessExcelImportJob + Storage بدل معالجة مباشرة
// بالطلب) عند الإنتاج الفعلي، خصوصًا لملفات بمئات/آلاف الصفوف.

return [
    'expected_columns' => [
        'lesson_id', 'game_type', 'question_text', 'difficulty', 'skill_name',
        'option_a', 'option_b', 'option_c', 'option_d', 'correct_option', 'correct_answer', 'explanation',
    ],

    'supported_game_types' => ['mcq', 'true_false'],

    'max_upload_rows' => 2000, // حماية مؤقتة ضد ملفات ضخمة بمعالجة متزامنة (انظر TODO أعلاه)
];
