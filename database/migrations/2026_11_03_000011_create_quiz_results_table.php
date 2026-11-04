<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->unsignedInteger('score');
            $table->unsignedInteger('max_score');
            $table->unsignedTinyInteger('percent');
            $table->boolean('passed');
            // Суралцагчийн сонголтууд: {"асуултын id": сонгосон индекс}
            $table->json('answers');
            $table->timestamp('submitted_at');
            $table->timestamps();

            // Давтан өгөхийг зөвшөөрөх тул unique биш.
            $table->index(['user_id', 'quiz_id']);
            $table->index(['quiz_id', 'passed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_results');
    }
};
