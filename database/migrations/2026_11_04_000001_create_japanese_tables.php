<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Япон текстийн давхцахгүй баганууд utf8mb4_bin: unicode_ci нь か/が, ア/あ-г ижил гэж үздэг.
 *
 * Япон хэлний модуль. users хүснэгтийг дахин ашиглана. courses/lessons/progress нь
 * видео хичээлд зориулагдсан тул үсэг, үг, дүрмийн бүтэцтэй агуулгыг тусад нь хадгална.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('japanese_levels', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('position')->default(1);
            $table->string('status', 20)->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('japanese_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('position')->default(1);
            $table->timestamps();
        });

        Schema::create('japanese_characters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('japanese_levels')->cascadeOnDelete();
            $table->string('script', 20);
            $table->string('character', 10)->collation('utf8mb4_bin');
            $table->string('romaji', 20);
            $table->string('reading_mn', 20);
            $table->string('row_name', 40);
            $table->unsignedSmallInteger('position')->default(1);
            $table->string('example_word', 60)->nullable();
            $table->string('example_reading', 60)->nullable();
            $table->string('example_romaji', 60)->nullable();
            $table->string('example_mn')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->unique(['script', 'character']);
            $table->index(['level_id', 'script', 'status', 'position']);
        });

        Schema::create('japanese_kanji', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('japanese_levels')->cascadeOnDelete();
            $table->string('kanji', 10)->collation('utf8mb4_bin');
            $table->string('onyomi', 60)->nullable();
            $table->string('kunyomi', 60)->nullable();
            $table->string('reading', 60);
            $table->string('romaji', 60);
            $table->string('meaning_mn');
            $table->string('example_word', 60)->nullable();
            $table->string('example_reading', 60)->nullable();
            $table->string('example_mn')->nullable();
            $table->text('example_sentence')->nullable();
            $table->text('example_sentence_reading')->nullable();
            $table->text('example_sentence_mn')->nullable();
            $table->unsignedSmallInteger('position')->default(1);
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->unique(['level_id', 'kanji']);
            $table->index(['level_id', 'status', 'position']);
        });

        Schema::create('japanese_vocabulary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('japanese_levels')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('japanese_categories')->restrictOnDelete();
            $table->string('word', 60)->collation('utf8mb4_bin');
            $table->string('reading', 60)->collation('utf8mb4_bin');
            $table->string('katakana', 60)->nullable();
            $table->string('romaji', 60);
            $table->string('meaning_mn');
            $table->text('example_ja')->nullable();
            $table->text('example_reading')->nullable();
            $table->text('example_romaji')->nullable();
            $table->text('example_mn')->nullable();
            $table->unsignedSmallInteger('position')->default(1);
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->unique(['level_id', 'word', 'reading']);
            $table->index(['level_id', 'category_id', 'status']);
        });

        Schema::create('japanese_grammar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('japanese_levels')->cascadeOnDelete();
            $table->string('title', 100)->collation('utf8mb4_bin');
            $table->string('structure');
            $table->text('explanation_mn');
            $table->text('usage_mn')->nullable();
            $table->unsignedSmallInteger('position')->default(1);
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->unique(['level_id', 'title']);
        });

        Schema::create('japanese_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('japanese_levels')->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('title');
            $table->text('intro_mn')->nullable();
            $table->unsignedSmallInteger('position')->default(1);
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->unique(['level_id', 'type', 'title']);
        });

        Schema::create('japanese_examples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grammar_id')->nullable()->constrained('japanese_grammar')->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained('japanese_lessons')->cascadeOnDelete();
            $table->string('speaker', 60)->nullable();
            $table->text('ja');
            $table->text('reading');
            $table->text('romaji');
            $table->text('mn');
            $table->unsignedSmallInteger('position')->default(1);
            $table->timestamps();
        });

        Schema::create('japanese_audio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('japanese_levels')->cascadeOnDelete();
            $table->string('title');
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->text('transcript_ja');
            $table->text('reading');
            $table->text('romaji');
            $table->text('translation_mn');
            $table->unsignedSmallInteger('position')->default(1);
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->unique(['level_id', 'title']);
        });

        Schema::create('japanese_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('japanese_levels')->cascadeOnDelete();
            $table->foreignId('audio_id')->nullable()->constrained('japanese_audio')->nullOnDelete();
            $table->string('type', 20);
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('pass_percent')->default(60);
            $table->unsignedSmallInteger('position')->default(1);
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->unique(['level_id', 'title']);
        });

        Schema::create('japanese_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('japanese_quizzes')->cascadeOnDelete();
            $table->string('kind', 20)->default('choice');
            $table->text('prompt');
            $table->text('prompt_ja')->nullable();
            $table->text('explanation_mn')->nullable();
            $table->unsignedSmallInteger('points')->default(1);
            $table->unsignedSmallInteger('position')->default(1);
            $table->timestamps();

            $table->index(['quiz_id', 'position']);
        });

        Schema::create('japanese_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('japanese_questions')->cascadeOnDelete();
            $table->string('text');
            $table->string('lang', 10)->default('ja');
            $table->string('match_text')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('position')->default(1);
            $table->timestamps();
        });

        Schema::create('japanese_user_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 20);
            $table->unsignedBigInteger('item_id');
            $table->timestamp('last_studied_at');
            $table->timestamps();

            $table->unique(['user_id', 'item_type', 'item_id']);
            $table->index(['user_id', 'last_studied_at']);
        });

        Schema::create('japanese_flashcards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vocabulary_id')->constrained('japanese_vocabulary')->cascadeOnDelete();
            $table->string('status', 20);
            $table->unsignedInteger('reviews')->default(0);
            $table->timestamp('last_reviewed_at');
            $table->timestamps();

            $table->unique(['user_id', 'vocabulary_id']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('japanese_test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained('japanese_quizzes')->cascadeOnDelete();
            $table->unsignedInteger('score');
            $table->unsignedInteger('max_score');
            $table->unsignedTinyInteger('percent');
            $table->boolean('passed');
            $table->json('answers');
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->index(['user_id', 'submitted_at']);
        });

        Schema::table('user_settings', function (Blueprint $table) {
            $table->boolean('tts_enabled')->default(false)->after('tts_voice');
            $table->string('tts_voice_ja')->nullable()->after('tts_enabled');
            $table->decimal('tts_volume', 3, 2)->default(1.00)->after('tts_voice_ja');
            $table->boolean('tts_read_japanese')->default(true)->after('tts_volume');
            $table->boolean('tts_read_mongolian')->default(true)->after('tts_read_japanese');
            $table->boolean('tts_highlight')->default(true)->after('tts_read_mongolian');
            $table->foreignId('japanese_level_id')->nullable()->after('reduce_motion')->constrained('japanese_levels')->nullOnDelete();
            $table->unsignedSmallInteger('japanese_daily_goal')->default(10)->after('japanese_level_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('japanese_level_id');
            $table->dropColumn(['tts_enabled', 'tts_voice_ja', 'tts_volume', 'tts_read_japanese', 'tts_read_mongolian', 'tts_highlight', 'japanese_daily_goal']);
        });

        foreach (['test_results', 'flashcards', 'user_progress', 'answers', 'questions', 'quizzes', 'audio', 'examples', 'lessons', 'grammar', 'vocabulary', 'kanji', 'characters', 'categories', 'levels'] as $name) {
            Schema::dropIfExists("japanese_{$name}");
        }
    }
};
