<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookmarkController extends Controller
{
    public function index(Request $request): View
    {
        return view('student.bookmarks', [
            'bookmarks' => $request->user()->bookmarks()
                ->whereHas('lesson', fn ($lesson) => $lesson->published()->whereHas('course', fn ($course) => $course->published()))
                ->with('lesson.course')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function toggle(Request $request, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->is_published && $lesson->course->is_published, 404);

        $bookmark = $request->user()->bookmarks()->where('lesson_id', $lesson->id)->first();

        if ($bookmark) {
            $bookmark->delete();

            return back()->with('status', 'Хичээлийг хадгалсан жагсаалтаас хаслаа.');
        }

        $request->user()->bookmarks()->create(['lesson_id' => $lesson->id]);

        return back()->with('status', 'Хичээлийг хадгаллаа.');
    }
}
