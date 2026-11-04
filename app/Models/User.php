<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * role болон is_active-г санаатайгаар оруулаагүй: хэрэглэгч бүртгүүлэх
     * form-оор өөрийгөө админ болгох боломжийг хаана.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'student',
        'is_active' => true,
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Хэрэглэгч бүр хүртээмжийн тохиргоотой байна.
     */
    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->settings()->create();
        });

        // Токены хүснэгтэд гадаад түлхүүр байхгүй тул гараар устгана.
        static::deleted(function (User $user) {
            $user->tokens()->delete();
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Хэрэглэгчийн эрхэд тохирох dashboard-ын хаяг.
     */
    public function dashboardUrl(): string
    {
        return route($this->isAdmin() ? 'admin.dashboard' : 'student.dashboard');
    }

    /**
     * Үзсэн хувь болон хичээлийн уртаас тооцно: буцааж гүйлгэхэд буурахгүй.
     * Урт нь тодорхойгүй хичээлд сүүлийн байрлалыг ашиглана.
     */
    public function watchedSeconds(): int
    {
        return (int) Progress::where('progress.user_id', $this->id)
            ->join('lessons', 'lessons.id', '=', 'progress.lesson_id')
            ->sum(DB::raw('COALESCE(progress.percent * lessons.duration_seconds / 100, progress.last_position_seconds)'));
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function bookmarkedLessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'bookmarks')->withTimestamps();
    }

    public function quizResults(): HasMany
    {
        return $this->hasMany(QuizResult::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }
}
