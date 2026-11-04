<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('language', 10)->default('mn');
            $table->string('disk', 30)->default('local');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamps();

            // Нэг хичээлд нэг хэлний нэг төрлийн файл л байна.
            $table->unique(['lesson_id', 'type', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_media');
    }
};
