<?php

namespace App\Http\Controllers\Admin\Japanese;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Japanese\Flashcard;
use App\Models\Japanese\TestResult;
use App\Models\Japanese\UserProgress;
use App\Models\User;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function __invoke(): View
    {
        $students = User::where('role', UserRole::Student)
            ->select(['id', 'name'])
            ->addSelect([
                'studied' => UserProgress::selectRaw('count(*)')->whereColumn('user_id', 'users.id'),
                'known_words' => Flashcard::selectRaw('count(*)')->whereColumn('user_id', 'users.id')->where('status', 'known'),
                'tests' => TestResult::selectRaw('count(*)')->whereColumn('user_id', 'users.id'),
                'average' => TestResult::selectRaw('round(avg(percent))')->whereColumn('user_id', 'users.id'),
                'last_activity' => UserProgress::selectRaw('max(last_studied_at)')->whereColumn('user_id', 'users.id'),
            ])
            ->orderBy('name')
            ->paginate(30);

        return view('admin.japanese.progress', ['students' => $students]);
    }
}
