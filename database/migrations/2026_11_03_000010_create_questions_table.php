<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->text('body');
            // Хариултын сонголтууд: ["А хариулт", "Б хариулт", ...]
            $table->json('options');
            // options массив дахь зөв хариултын индекс (0-ээс эхэлнэ).
            $table->unsignedTinyInteger('correct_option');
            $table->unsignedSmallInteger('points')->default(1);
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();

            $table->index(['quiz_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
