<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LessonController as AdminLessonController;
use App\Http\Controllers\Admin\LessonMediaController as AdminLessonMediaController;
use App\Http\Controllers\Admin\Japanese\AudioFileController as JapaneseAudioFileController;
use App\Http\Controllers\Admin\Japanese\ContentController as JapaneseContentController;
use App\Http\Controllers\Admin\Japanese\ProgressController as JapaneseProgressController;
use App\Http\Controllers\Admin\Japanese\QuestionController as JapaneseQuestionController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\SystemSettingController as AdminSystemSettingController;
use App\Http\Controllers\Student\ProgressController as StudentProgressController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Student\FeedbackController as StudentFeedbackController;
use App\Support\Reports\ReportBuilder;
use App\Http\Controllers\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\Admin\QuizResultController as AdminQuizResultController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\QuizController as StudentQuizController;
use App\Http\Controllers\Japanese\FlashcardController as JapaneseFlashcardController;
use App\Http\Controllers\Japanese\QuizController as JapaneseQuizController;
use App\Http\Controllers\Japanese\StudyController as JapaneseStudyController;
use App\Support\Japanese\AdminResources;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Student\BookmarkController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\MyCourseController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::view('/about', 'about')->name('about');

Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:10,1')
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->middleware('throttle:10,1')->name('profile.password');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('/courses/{course:slug}/lessons/{lesson}', [LessonController::class, 'show'])
        ->scopeBindings()
        ->name('lessons.show');
    Route::get('/media/{media}', [MediaController::class, 'show'])->name('media.show');

    // Япон хэл: нэвтэрсэн хэрэглэгч уншина, явц бичих нь зөвхөн суралцагчид.
    Route::prefix('japanese')->name('japanese.')->group(function () {
        Route::get('/', [JapaneseStudyController::class, 'index'])->name('index');
        Route::get('/audio/{audio}/file', [JapaneseStudyController::class, 'audioFile'])->name('audio.file');

        Route::middleware('role:student')->group(function () {
            Route::post('/preferences', [JapaneseStudyController::class, 'setLevel'])->name('preferences');
            Route::post('/progress', [JapaneseStudyController::class, 'toggleProgress'])->name('progress');
            Route::post('/quizzes/{quiz}/check', [JapaneseQuizController::class, 'check'])->name('quizzes.check');
            Route::post('/{level:code}/quizzes/{quiz}', [JapaneseQuizController::class, 'submit'])->name('quizzes.submit');
            Route::post('/{level:code}/flashcards/{vocabulary}', [JapaneseFlashcardController::class, 'update'])->name('flashcards.update');
        });

        Route::prefix('{level:code}')->group(function () {
            Route::get('/', [JapaneseStudyController::class, 'level'])->name('level');
            Route::get('/kana/{script}', [JapaneseStudyController::class, 'kana'])->whereIn('script', ['hiragana', 'katakana'])->name('kana');
            Route::get('/characters/{character}', [JapaneseStudyController::class, 'character'])->name('character');
            Route::get('/vocabulary', [JapaneseStudyController::class, 'vocabulary'])->name('vocabulary');
            Route::get('/flashcards', [JapaneseFlashcardController::class, 'show'])->name('flashcards');
            Route::get('/kanji', [JapaneseStudyController::class, 'kanji'])->name('kanji');
            Route::get('/kanji/{kanji}', [JapaneseStudyController::class, 'kanjiShow'])->name('kanji.show');
            Route::get('/grammar', [JapaneseStudyController::class, 'grammar'])->name('grammar');
            Route::get('/grammar/{grammar}', [JapaneseStudyController::class, 'grammarShow'])->name('grammar.show');
            Route::get('/listening', [JapaneseStudyController::class, 'listening'])->name('listening');
            Route::get('/listening/{audio}', [JapaneseStudyController::class, 'listeningShow'])->name('listening.show');
            Route::get('/lessons/{type}', [JapaneseStudyController::class, 'lessons'])->whereIn('type', ['reading', 'conversation', 'daily'])->name('lessons');
            Route::get('/lesson/{lesson}', [JapaneseStudyController::class, 'lesson'])->name('lesson');
            Route::get('/quizzes', [JapaneseQuizController::class, 'index'])->name('quizzes');
            Route::get('/quizzes/{quiz}', [JapaneseQuizController::class, 'show'])->name('quizzes.show');
            Route::get('/results/{result}', [JapaneseQuizController::class, 'result'])->name('quizzes.result');
        });
    });

    Route::middleware('role:student')->group(function () {
        Route::get('/dashboard', StudentDashboardController::class)->name('student.dashboard');
        Route::get('/my-courses', MyCourseController::class)->name('student.my-courses');
        Route::get('/progress', StudentProgressController::class)->name('student.progress');
        Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('student.bookmarks');
        Route::get('/feedback', [StudentFeedbackController::class, 'index'])->name('student.feedback');
        Route::post('/feedback', [StudentFeedbackController::class, 'store'])->middleware('throttle:10,1')->name('student.feedback.store');
        Route::get('/quizzes', [StudentQuizController::class, 'index'])->name('student.quizzes');
        Route::get('/courses/{course:slug}/quizzes/{quiz}', [StudentQuizController::class, 'show'])->scopeBindings()->name('quizzes.show');
        Route::post('/courses/{course:slug}/quizzes/{quiz}', [StudentQuizController::class, 'submit'])->scopeBindings()->name('quizzes.submit');
        Route::get('/quiz-results/{result}', [StudentQuizController::class, 'result'])->name('quizzes.result');
        Route::post('/lessons/{lesson}/bookmark', [BookmarkController::class, 'toggle'])->name('lessons.bookmark');
        Route::post('/lessons/{lesson}/progress', [LessonController::class, 'saveProgress'])->name('lessons.progress');
        Route::post('/lessons/{lesson}/complete', [LessonController::class, 'complete'])->name('lessons.complete');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::patch('/users/{user}/active', [AdminUserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::resource('users', AdminUserController::class)->except('show');

        Route::resource('categories', AdminCategoryController::class)->except('show');
        Route::resource('courses', AdminCourseController::class)->except('show');

        Route::get('/courses/{course}/lessons', [AdminLessonController::class, 'index'])->name('courses.lessons.index');
        Route::get('/courses/{course}/lessons/create', [AdminLessonController::class, 'create'])->name('courses.lessons.create');
        Route::post('/courses/{course}/lessons', [AdminLessonController::class, 'store'])->name('courses.lessons.store');
        Route::get('/lessons/{lesson}/edit', [AdminLessonController::class, 'edit'])->name('lessons.edit');
        Route::put('/lessons/{lesson}', [AdminLessonController::class, 'update'])->name('lessons.update');
        Route::delete('/lessons/{lesson}', [AdminLessonController::class, 'destroy'])->name('lessons.destroy');
        Route::patch('/lessons/{lesson}/move', [AdminLessonController::class, 'move'])->name('lessons.move');

        Route::get('/courses/{course}/quizzes', [AdminQuizController::class, 'index'])->name('courses.quizzes.index');
        Route::get('/courses/{course}/quizzes/create', [AdminQuizController::class, 'create'])->name('courses.quizzes.create');
        Route::post('/courses/{course}/quizzes', [AdminQuizController::class, 'store'])->name('courses.quizzes.store');
        Route::get('/quizzes/{quiz}/edit', [AdminQuizController::class, 'edit'])->name('quizzes.edit');
        Route::put('/quizzes/{quiz}', [AdminQuizController::class, 'update'])->name('quizzes.update');
        Route::delete('/quizzes/{quiz}', [AdminQuizController::class, 'destroy'])->name('quizzes.destroy');
        Route::patch('/quizzes/{quiz}/publish', [AdminQuizController::class, 'toggle'])->name('quizzes.toggle');
        Route::get('/quizzes/{quiz}/questions/create', [AdminQuestionController::class, 'create'])->name('quizzes.questions.create');
        Route::post('/quizzes/{quiz}/questions', [AdminQuestionController::class, 'store'])->name('quizzes.questions.store');
        Route::get('/questions/{question}/edit', [AdminQuestionController::class, 'edit'])->name('questions.edit');
        Route::put('/questions/{question}', [AdminQuestionController::class, 'update'])->name('questions.update');
        Route::delete('/questions/{question}', [AdminQuestionController::class, 'destroy'])->name('questions.destroy');
        Route::get('/quiz-results', [AdminQuizResultController::class, 'index'])->name('quiz-results.index');

        Route::get('/settings', [AdminSystemSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [AdminSystemSettingController::class, 'update'])->name('settings.update');

        Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
        Route::get('/feedback/{feedback}', [AdminFeedbackController::class, 'show'])->name('feedback.show');
        Route::put('/feedback/{feedback}', [AdminFeedbackController::class, 'reply'])->name('feedback.reply');

        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{report}', [AdminReportController::class, 'show'])->whereIn('report', ReportBuilder::KEYS)->name('reports.show');
        Route::get('/reports/{report}/download.{format}', [AdminReportController::class, 'download'])
            ->whereIn('report', ReportBuilder::KEYS)
            ->whereIn('format', ['xlsx', 'pdf'])
            ->name('reports.download');

        Route::get('/lessons/{lesson}/media', [AdminLessonMediaController::class, 'index'])->name('lessons.media');
        Route::post('/lessons/{lesson}/media', [AdminLessonMediaController::class, 'store'])->name('lessons.media.store');
        Route::delete('/media/{media}', [AdminLessonMediaController::class, 'destroy'])->name('media.destroy');

        Route::prefix('japanese')->name('japanese.')->group(function () {
            Route::get('/', [JapaneseContentController::class, 'hub'])->name('hub');
            Route::get('/progress', JapaneseProgressController::class)->name('progress');

            Route::post('/audio/{audio}/file', [JapaneseAudioFileController::class, 'store'])->name('audio.file.store');
            Route::delete('/audio/{audio}/file', [JapaneseAudioFileController::class, 'destroy'])->name('audio.file.destroy');

            Route::get('/quizzes/{quiz}/questions/create', [JapaneseQuestionController::class, 'create'])->name('questions.create');
            Route::post('/quizzes/{quiz}/questions', [JapaneseQuestionController::class, 'store'])->name('questions.store');
            Route::get('/questions/{question}/edit', [JapaneseQuestionController::class, 'edit'])->name('questions.edit');
            Route::put('/questions/{question}', [JapaneseQuestionController::class, 'update'])->name('questions.update');
            Route::delete('/questions/{question}', [JapaneseQuestionController::class, 'destroy'])->name('questions.destroy');

            $resources = AdminResources::KEYS;

            Route::prefix('{resource}')->whereIn('resource', $resources)->group(function () {
                Route::get('/', [JapaneseContentController::class, 'index'])->name('index');
                Route::get('/create', [JapaneseContentController::class, 'create'])->name('create');
                Route::post('/', [JapaneseContentController::class, 'store'])->name('store');
                Route::post('/publish-all', [JapaneseContentController::class, 'publishAll'])->name('publish-all');
                Route::get('/{id}/edit', [JapaneseContentController::class, 'edit'])->whereNumber('id')->name('edit');
                Route::put('/{id}', [JapaneseContentController::class, 'update'])->whereNumber('id')->name('update');
                Route::delete('/{id}', [JapaneseContentController::class, 'destroy'])->whereNumber('id')->name('destroy');
                Route::patch('/{id}/status', [JapaneseContentController::class, 'toggleStatus'])->whereNumber('id')->name('status');
            });
        });
    });
});
