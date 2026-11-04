<?php

namespace App\Models\Japanese;

use App\Models\Japanese\Concerns\HasStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Audio extends Model
{
    use HasStatus;

    protected $table = 'japanese_audio';

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::deleted(function (Audio $audio) {
            if ($audio->file_path) {
                Storage::disk('local')->delete($audio->file_path);
            }
        });
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'audio_id');
    }

    /**
     * Файлгүй сонсох дасгалыг нийтлэхгүй: байхгүй аудиог байгаа мэт харуулахгүй.
     */
    public function publishBlocker(): ?string
    {
        return $this->file_path ? null : 'Аудио файл оруулаагүй тул нийтлэх боломжгүй.';
    }
}
