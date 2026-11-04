<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Зөвхөн нэмэлт багана, шинэ хүснэгт: одоо байгаа өгөгдөлд нөлөөлөхгүй.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->string('font_style', 20)->default('default')->after('font_size_percent');
            $table->string('line_spacing', 20)->default('normal')->after('font_style');
            $table->boolean('accessibility_mode')->default(false)->after('line_spacing');
            $table->string('caption_size', 20)->default('normal')->after('subtitles_enabled');
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');

        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn(['font_style', 'line_spacing', 'accessibility_mode', 'caption_size']);
        });
    }
};
