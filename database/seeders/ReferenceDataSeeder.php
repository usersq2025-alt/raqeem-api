<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferenceDataSeeder extends Seeder
{
    /**
     * Re-seed the lookup/reference data that already existed in the
     * hand-built schema before it was captured into migrations.
     */
    public function run(): void
    {
        DB::table('admin_roles')->insert([
            ['id' => 1, 'code' => 'super_admin', 'name_ar' => 'مشرف عام', 'name_en' => 'Super Admin'],
            ['id' => 2, 'code' => 'content_manager', 'name_ar' => 'مدير محتوى', 'name_en' => 'Content Manager'],
            ['id' => 3, 'code' => 'teacher', 'name_ar' => 'معلّم', 'name_en' => 'Teacher'],
            ['id' => 4, 'code' => 'reviewer', 'name_ar' => 'مراجع', 'name_en' => 'Reviewer'],
            ['id' => 5, 'code' => 'support', 'name_ar' => 'دعم', 'name_en' => 'Support'],
        ]);

        DB::table('grades')->insert([
            ['id' => 1, 'level' => 1, 'name_ar' => 'الصف الأول', 'name_en' => 'Grade 1', 'is_active' => 1],
            ['id' => 2, 'level' => 2, 'name_ar' => 'الصف الثاني', 'name_en' => 'Grade 2', 'is_active' => 1],
            ['id' => 3, 'level' => 3, 'name_ar' => 'الصف الثالث', 'name_en' => 'Grade 3', 'is_active' => 1],
            ['id' => 4, 'level' => 4, 'name_ar' => 'الصف الرابع', 'name_en' => 'Grade 4', 'is_active' => 1],
            ['id' => 5, 'level' => 5, 'name_ar' => 'الصف الخامس', 'name_en' => 'Grade 5', 'is_active' => 1],
            ['id' => 6, 'level' => 6, 'name_ar' => 'الصف السادس', 'name_en' => 'Grade 6', 'is_active' => 1],
        ]);

        DB::table('professions')->insert([
            ['id' => 1, 'code' => 'doctor', 'name_ar' => 'طبيب', 'name_en' => 'Doctor', 'avatar_asset_url' => '/avatars/doctor.png', 'sort_order' => 1, 'is_active' => 1],
            ['id' => 2, 'code' => 'engineer', 'name_ar' => 'مهندس', 'name_en' => 'Engineer', 'avatar_asset_url' => '/avatars/engineer.png', 'sort_order' => 2, 'is_active' => 1],
            ['id' => 3, 'code' => 'teacher', 'name_ar' => 'معلّم', 'name_en' => 'Teacher', 'avatar_asset_url' => '/avatars/teacher.png', 'sort_order' => 3, 'is_active' => 1],
            ['id' => 4, 'code' => 'chef', 'name_ar' => 'شيف', 'name_en' => 'Chef', 'avatar_asset_url' => '/avatars/chef.png', 'sort_order' => 4, 'is_active' => 1],
            ['id' => 5, 'code' => 'astronaut', 'name_ar' => 'رائد فضاء', 'name_en' => 'Astronaut', 'avatar_asset_url' => '/avatars/astronaut.png', 'sort_order' => 5, 'is_active' => 1],
            ['id' => 6, 'code' => 'soldier', 'name_ar' => 'جندي', 'name_en' => 'Soldier', 'avatar_asset_url' => '/avatars/soldier.png', 'sort_order' => 6, 'is_active' => 1],
        ]);

        DB::table('subjects')->insert([
            ['id' => 1, 'name_ar' => 'اللغة العربية', 'name_en' => 'Arabic', 'icon_url' => null, 'sort_order' => 1, 'is_active' => 1],
            ['id' => 2, 'name_ar' => 'اللغة الإنكليزية', 'name_en' => 'English', 'icon_url' => null, 'sort_order' => 2, 'is_active' => 1],
            ['id' => 3, 'name_ar' => 'اللغة الفرنسية', 'name_en' => 'French', 'icon_url' => null, 'sort_order' => 3, 'is_active' => 1],
            ['id' => 4, 'name_ar' => 'الرياضيات', 'name_en' => 'Mathematics', 'icon_url' => null, 'sort_order' => 4, 'is_active' => 1],
            ['id' => 5, 'name_ar' => 'التربية الدينية', 'name_en' => 'Religious Education', 'icon_url' => null, 'sort_order' => 5, 'is_active' => 1],
            ['id' => 6, 'name_ar' => 'الدراسات الاجتماعية', 'name_en' => 'Social Studies', 'icon_url' => null, 'sort_order' => 6, 'is_active' => 1],
            ['id' => 7, 'name_ar' => 'العلوم', 'name_en' => 'Science', 'icon_url' => null, 'sort_order' => 7, 'is_active' => 1],
        ]);

        DB::table('game_types')->insert([
            ['id' => 1, 'code' => 'mcq', 'name_ar' => 'اختيار من متعدد', 'name_en' => 'Multiple Choice', 'is_active' => 1],
            ['id' => 2, 'code' => 'drag_classify', 'name_ar' => 'سحب وإفلات للتصنيف', 'name_en' => 'Drag & Drop Classification', 'is_active' => 1],
            ['id' => 3, 'code' => 'matching_pairs', 'name_ar' => 'توصيل قوائم متطابقة', 'name_en' => 'Matching Pairs', 'is_active' => 1],
            ['id' => 4, 'code' => 'memory_cards', 'name_ar' => 'بطاقات الذاكرة', 'name_en' => 'Memory Cards', 'is_active' => 1],
            ['id' => 5, 'code' => 'pointing', 'name_ar' => 'التأشير', 'name_en' => 'Pointing', 'is_active' => 1],
            ['id' => 6, 'code' => 'crossword', 'name_ar' => 'الكلمات المتقاطعة', 'name_en' => 'Crossword', 'is_active' => 1],
            ['id' => 7, 'code' => 'true_false', 'name_ar' => 'صح أو خطأ سريع', 'name_en' => 'Quick True/False', 'is_active' => 1],
            ['id' => 8, 'code' => 'custom', 'name_ar' => 'نمط إضافي حسب المادة', 'name_en' => 'Subject-specific Custom Type', 'is_active' => 1],
        ]);

        DB::table('settings')->insert([
            ['id' => 1, 'setting_key' => 'platform_name', 'setting_value' => 'رقيم', 'group_name' => 'general', 'updated_at' => now()],
            ['id' => 2, 'setting_key' => 'default_language', 'setting_value' => 'ar', 'group_name' => 'general', 'updated_at' => now()],
            ['id' => 3, 'setting_key' => 'primary_color', 'setting_value' => '#000000', 'group_name' => 'general', 'updated_at' => now()],
            ['id' => 4, 'setting_key' => 'otp_expiry_minutes', 'setting_value' => '5', 'group_name' => 'security', 'updated_at' => now()],
            ['id' => 5, 'setting_key' => 'lesson_recharge_minutes', 'setting_value' => '15', 'group_name' => 'gameplay', 'updated_at' => now()],
            ['id' => 6, 'setting_key' => 'review_station_points_ratio', 'setting_value' => '0.5', 'group_name' => 'gameplay', 'updated_at' => now()],
        ]);
    }
}
