<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Course extends Model
{
    protected $fillable = [
        'category_id',
        'created_by',
        'title',
        'slug',
        'description',
        'thumbnail_path',
        'thumbnail_alt',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    /**
     * Өгөгдлийн сангийн cascade нь файлыг устгадаггүй тул Eloquent-ээр устгана.
     */
    protected static function booted(): void
    {
        static::deleting(function (Course $course) {
            $course->lessons()->get()->each->delete();

            if ($course->thumbnail_path) {
                Storage::disk('public')->delete($course->thumbnail_path);
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Гарчиг эсвэл тайлбараар хайна. Хэрэглэгчийн оруулсан % болон _ тэмдэгтийг
     * LIKE-ийн тусгай тэмдэгт гэж тооцохгүй.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.addcslashes($term, '\\%_').'%';

        return $query->where(fn (Builder $q) => $q
            ->where('title', 'like', $like)
            ->orWhere('description', 'like', $like));
    }

    /**
     * Суралцагчийн эхэлсэн сургалтууд, явцын тоо болон сүүлд үзсэн цагтай.
     * Хамгийн сүүлд үзсэн нь эхэндээ байна.
     */
    public function scopeStartedBy(Builder $query, User $user): Builder
    {
        $ofUser = fn ($progress) => $progress->where('user_id', $user->id);

        return $query
            ->select('courses.*')
            ->published()
            ->whereHas('publishedLessons.progress', $ofUser)
            ->withCount([
                'publishedLessons as lessons_total',
                'publishedLessons as lessons_completed' => fn ($lessons) => $lessons->whereHas(
                    'progress',
                    fn ($progress) => $ofUser($progress)->where('is_completed', true)
                ),
            ])
            ->addSelect(['last_watched_at' => Progress::query()
                ->select('progress.last_watched_at')
                ->join('lessons', 'lessons.id', '=', 'progress.lesson_id')
                ->whereColumn('lessons.course_id', 'courses.id')
                ->where('progress.user_id', $user->id)
                ->orderByDesc('progress.last_watched_at')
                ->limit(1),
            ])
            ->orderByDesc('last_watched_at');
    }

    /**
     * scopeStartedBy-ийн тоонуудаас тооцсон дуусгалтын хувь.
     */
    public function getCompletionPercentAttribute(): int
    {
        $total = (int) ($this->lessons_total ?? 0);

        return $total === 0 ? 0 : (int) floor(((int) $this->lessons_completed) * 100 / $total);
    }

    /**
     * Суралцагчийн дуусгаагүй эхний хичээл, бүгд дууссан бол эхний хичээл.
     */
    public function nextLessonFor(User $user): ?Lesson
    {
        return $this->publishedLessons()
            ->whereDoesntHave('progress', fn ($progress) => $progress
                ->where('user_id', $user->id)
                ->where('is_completed', true))
            ->first() ?? $this->publishedLessons()->first();
    }

    public function publishedLessons(): HasMany
    {
        return $this->lessons()->where('is_published', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }
}
