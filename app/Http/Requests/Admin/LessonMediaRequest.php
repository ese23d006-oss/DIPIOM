<?php

namespace App\Http\Requests\Admin;

use App\Enums\MediaType;
use App\Support\UploadLimit;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class LessonMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $type = MediaType::tryFrom((string) $this->input('type'));

        return [
            'type' => ['required', Rule::enum(MediaType::class)],
            'duration_seconds' => ['nullable', 'integer', 'min:1', 'max:86400'],
            'file' => array_filter([
                'required',
                'file',
                $type ? 'extensions:'.implode(',', $type->extensions()) : null,
                // Өргөтгөлийг солисон файлыг агуулгаар нь илрүүлнэ.
                $type && $type->mimeTypes() ? 'mimetypes:'.implode(',', $type->mimeTypes()) : null,
                $type ? 'max:'.$type->maxKilobytes() : null,
                $type === MediaType::Subtitle ? $this->validVtt(...) : null,
            ]),
        ];
    }

    /**
     * Техникийн MIME нэрийн оронд хэрэглэгчид ойлгомжтой тайлбар.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $type = MediaType::tryFrom((string) $this->input('type'));
        $allowed = $type ? strtoupper(implode(', ', $type->extensions())) : '';
        $wrongType = "Энэ файлын төрөл зөвшөөрөгдөхгүй. Зөвшөөрөгдөх төрөл: {$allowed}.";

        return [
            'file.required' => 'Файл сонгоно уу.',
            // PHP-ийн хязгаар давсан үед гол шалтгаан нь хэмжээ байдаг.
            'file.uploaded' => 'Файлыг оруулж чадсангүй. Хэмжээ нь хэт том эсвэл холболт тасарсан байж болзошгүй. Хамгийн ихдээ '.($type ? UploadLimit::label($type->maxKilobytes()) : '').'.',
            'file.extensions' => $wrongType,
            'file.mimetypes' => $wrongType,
            'file.max' => 'Файл хэт том байна. Хамгийн ихдээ '.($type ? UploadLimit::label($type->maxKilobytes()) : '').' байна.',
        ];
    }

    /**
     * WebVTT файл заавал "WEBVTT"-ээр эхэлж, UTF-8 байх ёстой.
     */
    private function validVtt(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        $content = (string) file_get_contents($value->getRealPath());

        if (! mb_check_encoding($content, 'UTF-8')) {
            $fail('Хадмал файл UTF-8 кодчилолтой байх ёстой.');

            return;
        }

        if (! preg_match('/^(\xEF\xBB\xBF)?WEBVTT(\s|$)/', $content)) {
            $fail('Хадмал файл зөв WebVTT форматтай биш байна. Файлын эхний мөр WEBVTT байх ёстой.');
        }
    }
}
