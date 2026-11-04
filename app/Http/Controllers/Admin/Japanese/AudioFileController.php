<?php

namespace App\Http\Controllers\Admin\Japanese;

use App\Enums\MediaType;
use App\Http\Controllers\Controller;
use App\Models\Japanese\Audio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AudioFileController extends Controller
{
    public function store(Request $request, Audio $audio): RedirectResponse
    {
        $type = MediaType::AudioDescription;
        $allowed = strtoupper(implode(', ', $type->extensions()));

        $request->validate([
            'file' => ['required', 'file', 'extensions:'.implode(',', $type->extensions()), 'mimetypes:'.implode(',', $type->mimeTypes()), 'max:'.$type->maxKilobytes()],
        ], [
            'file.required' => 'Файл сонгоно уу.',
            'file.extensions' => "Энэ файлын төрөл зөвшөөрөгдөхгүй. Зөвшөөрөгдөх төрөл: {$allowed}.",
            'file.mimetypes' => "Энэ файлын төрөл зөвшөөрөгдөхгүй. Зөвшөөрөгдөх төрөл: {$allowed}.",
        ]);

        $file = $request->file('file');
        $old = $audio->file_path;

        $audio->fill([
            'file_path' => $file->storeAs('japanese-audio', Str::random(40).'.'.strtolower($file->getClientOriginalExtension()), 'local'),
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
        ])->save();

        if ($old) {
            Storage::disk('local')->delete($old);
        }

        return back()->with('status', 'Аудио файлыг орууллаа.');
    }

    public function destroy(Audio $audio): RedirectResponse
    {
        if ($audio->file_path) {
            Storage::disk('local')->delete($audio->file_path);
        }

        // Файлгүй болсон дасгал нийтлэгдсэн хэвээр үлдэхгүй.
        $audio->fill(['file_path' => null, 'original_name' => null, 'mime_type' => null, 'size_bytes' => null, 'status' => 'draft'])->save();

        return back()->with('status', 'Аудио файлыг устгаж, дасгалыг ноорог болголоо.');
    }
}
