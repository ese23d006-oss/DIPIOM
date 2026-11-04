<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Зөвхөн системд зайлшгүй хэрэгтэй анхны өгөгдлийг оруулна: админ болон
 * засаж, устгаж болох үндсэн ангиллууд. Хуурамч сургалт, суралцагч оруулахгүй.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdmin();
        $this->seedCategories();
        $this->call(JapaneseN5Seeder::class);
    }

    private function seedAdmin(): void
    {
        $email = config('lms.admin.email');
        $password = config('lms.admin.password');

        if (blank($email) || blank($password)) {
            throw new RuntimeException('.env файлд ADMIN_EMAIL болон ADMIN_PASSWORD утгыг тохируулна уу.');
        }

        // Дахин ажиллуулахад одоо байгаа админы нууц үгийг дарж бичихгүй.
        $admin = User::firstOrNew(['email' => $email]);

        if ($admin->exists) {
            return;
        }

        $admin->name = config('lms.admin.name');
        $admin->password = $password;
        $admin->role = UserRole::Admin;
        $admin->is_active = true;
        $admin->email_verified_at = now();
        $admin->save();
    }

    private function seedCategories(): void
    {
        $categories = [
            'computer-skills' => ['Компьютерийн мэдлэг', 'Компьютер, дэлгэц уншигч программ ашиглах ур чадвар.'],
            'languages' => ['Хэл', 'Гадаад болон Монгол хэлний сургалтууд.'],
            'professional-skills' => ['Мэргэжлийн ур чадвар', 'Ажил мэргэжилд хэрэгтэй мэдлэг, ур чадвар.'],
            'daily-life' => ['Өдөр тутмын амьдрал', 'Бие даан амьдрахад хэрэгтэй мэдлэг.'],
        ];

        foreach ($categories as $slug => [$name, $description]) {
            Category::firstOrCreate(['slug' => $slug], ['name' => $name, 'description' => $description]);
        }
    }
}
