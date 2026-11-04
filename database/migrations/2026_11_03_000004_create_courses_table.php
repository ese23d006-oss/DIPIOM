<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            // Сургалттай ангиллыг санамсаргүй устгахаас хамгаална.
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('thumbnail_path')->nullable();
            $table->string('thumbnail_alt')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index(['is_published', 'category_id']);
            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
