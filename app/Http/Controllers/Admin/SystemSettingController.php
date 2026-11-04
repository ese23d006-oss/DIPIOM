<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Support\UploadLimit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function edit(): View
    {
        // Удирдаж болохгүй ч анхаарах ёстой орчны төлөв: нууц утга харуулахгүй.
        $mailer = config('mail.default');

        return view('admin.settings', [
            'registrationOpen' => SystemSetting::registrationOpen(),
            'supportContact' => SystemSetting::supportContact(),
            'checks' => [
                ['Имэйл илгээлт', $mailer === 'log' ? 'Тохируулаагүй: нууц үг сэргээх имэйл зөвхөн логт бичигдэнэ' : "Идэвхтэй ({$mailer})", $mailer !== 'log'],
                ['Алдааны дэлгэрэнгүй (APP_DEBUG)', config('app.debug') ? 'Асаалттай: серверт унтраах ёстой' : 'Унтраалттай', ! config('app.debug')],
                ['HTTPS албадлага', config('lms.force_https') ? 'Асаалттай' : 'Унтраалттай: серверт асаах ёстой', (bool) config('lms.force_https')],
                ['Сургалтын зургийн холбоос (storage:link)', file_exists(public_path('storage')) ? 'Үүссэн' : 'Үүсгээгүй', file_exists(public_path('storage'))],
                ['Видеоны бодит дээд хэмжээ', UploadLimit::label(config('lms.uploads.video_kb')), true],
                ['Laravel, PHP хувилбар', app()->version().', PHP '.PHP_VERSION, true],
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(['support_contact' => ['nullable', 'string', 'max:255']], [], ['support_contact' => 'Холбоо барих мэдээлэл']);

        SystemSetting::put('registration_open', $request->boolean('registration_open') ? '1' : '0');
        SystemSetting::put('support_contact', $data['support_contact'] ?? null);

        return back()->with('status', 'Системийн тохиргоо хадгалагдлаа.');
    }
}
