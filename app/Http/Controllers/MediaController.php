<?php

namespace App\Http\Controllers;

use App\Models\LessonMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    /**
     * Хичээлийн файлыг зөвхөн нэвтэрсэн хэрэглэгчид өгнө. Range хүсэлтийг
     * дэмждэг тул видеог дундаас нь гүйлгэж болно.
     */
    public function show(Request $request, LessonMedia $media): BinaryFileResponse
    {
        $lesson = $media->lesson;

        abort_unless(
            $request->user()->isAdmin() || ($lesson->is_published && $lesson->course->is_published),
            404
        );

        $disk = Storage::disk($media->disk);

        abort_unless($disk->exists($media->file_path), 404);

        return response()->file($disk->path($media->file_path), [
            'Content-Type' => $media->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
