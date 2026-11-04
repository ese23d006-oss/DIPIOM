<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('font_size_percent')->default(100);
            $table->boolean('high_contrast')->default(false);
            $table->boolean('audio_description_enabled')->default(true);
            $table->boolean('subtitles_enabled')->default(true);
            $table->decimal('tts_rate', 3, 2)->default(1.00);
            $table->string('tts_voice')->nullable();
            $table->boolean('reduce_motion')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
