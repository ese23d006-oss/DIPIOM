<?php

use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\MediaController;
use App\Http\Middleware\EnsureApiUserIsActive;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->middleware('throttle:120,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login');

    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');

    Route::middleware(['auth:sanctum', EnsureApiUserIsActive::class])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::get('/lessons/{lesson}', [CourseController::class, 'lesson'])->name('lessons.show');
        Route::get('/media/{media}', [MediaController::class, 'show'])->name('media');

        Route::middleware('role:student')->group(function () {
            Route::get('/my-courses', [StudentController::class, 'myCourses'])->name('my-courses');
            Route::post('/lessons/{lesson}/progress', [LessonController::class, 'saveProgress'])->name('lessons.progress');
            Route::get('/bookmarks', [StudentController::class, 'bookmarks'])->name('bookmarks');
            Route::post('/lessons/{lesson}/bookmark', [StudentController::class, 'toggleBookmark'])->name('bookmarks.toggle');
            Route::get('/quizzes/{quiz}', [StudentController::class, 'quiz'])->name('quizzes.show');
            Route::post('/quizzes/{quiz}/submit', [StudentController::class, 'submitQuiz'])->name('quizzes.submit');
            Route::get('/quiz-results', [StudentController::class, 'results'])->name('quiz-results');
            Route::get('/settings', [StudentController::class, 'settings'])->name('settings');
            Route::put('/settings', [StudentController::class, 'updateSettings'])->name('settings.update');
            Route::get('/feedback', [StudentController::class, 'feedback'])->name('feedback');
            Route::post('/feedback', [StudentController::class, 'storeFeedback'])->name('feedback.store');
        });

        Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/stats', [AdminController::class, 'stats'])->name('stats');
            Route::get('/users', [AdminController::class, 'users'])->name('users');
        });
    });
});
