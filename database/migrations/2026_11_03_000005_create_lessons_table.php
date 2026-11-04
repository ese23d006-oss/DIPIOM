<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->longText('transcript')->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index(['course_id', 'is_published', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
