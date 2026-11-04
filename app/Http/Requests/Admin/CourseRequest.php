<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseRequest extends FormRequest
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
        $course = $this->route('course');

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            // SVG скрипт агуулж болох тул зөвшөөрөхгүй.
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:'.config('lms.uploads.image_kb')],
            // Зураг бүр утгатай тайлбартай байх ёстой.
            'thumbnail_alt' => [
                Rule::requiredIf(fn () => $this->hasFile('thumbnail')
                    || ($course?->thumbnail_path && ! $this->boolean('remove_thumbnail'))),
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
