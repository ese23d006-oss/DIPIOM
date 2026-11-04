<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MediaType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LessonMediaRequest;
use App\Models\Lesson;
use App\Models\LessonMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class LessonMediaController extends Controller
{
    private const DISK = 'local';

    public function index(Lesson $lesson): View
    {
        return view('admin.lessons.media', [
            'lesson' => $lesson,
            'course' => $lesson->course,
            'media' => $lesson->media()->get()->keyBy(fn (LessonMedia $item) => $item->type->value),
        ]);
    }

    public function store(LessonMediaRequest $request, Lesson $lesson): JsonResponse|RedirectResponse
    {
        $type = MediaType::from($request->validated('type'));
        $file = $request->file('file');

        // Нэрийг санамсаргүй болгож, өргөтгөлийг баталгаажсан жагсаалтаас авна.
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs("lesson-media/{$lesson->id}", Str::random(40).'.'.$extension, self::DISK);

        $media = LessonMedia::firstOrNew([
            'lesson_id' => $lesson->id,
            'type' => $type->value,
            'language' => 'mn',
        ]);
        $oldDisk = $media->exists ? $media->disk : null;
        $oldPath = $media->exists ? $media->file_path : null;

        $media->fill([
            'disk' => self::DISK,
            'file_path' => $path,
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'mime_type' => $type === MediaType::Subtitle ? 'text/vtt' : $file->getMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        try {
            $media->save();
        } catch (Throwable $exception) {
            Storage::disk(self::DISK)->delete($path);

            throw $exception;
        }

        if ($oldPath) {
            Storage::disk($oldDisk)->delete($oldPath);
        }

        if ($type === MediaType::Video) {
            // Хөтөч файлын уртыг мэдэгдэнэ. Мэдэгдээгүй бол хуучин утга буруу болно.
            $lesson->update(['duration_seconds' => $request->validated('duration_seconds')]);
        }

        $redirect = route('admin.lessons.media', $lesson);
        session()->flash('status', "{$type->label()} файлыг амжилттай орууллаа.");

        return $request->expectsJson()
            ? response()->json(['redirect' => $redirect])
            : redirect($redirect);
    }

    public function destroy(LessonMedia $media): RedirectResponse
    {
        $label = $media->type->label();

        if ($media->type === MediaType::Video) {
            $media->lesson->update(['duration_seconds' => null]);
        }

        $media->delete();

        return back()->with('status', "{$label} файлыг устгалаа.");
    }
}
