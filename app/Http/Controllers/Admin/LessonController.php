<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LessonRequest;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(Course $course): View
    {
        return view('admin.lessons.index', [
            'course' => $course,
            'lessons' => $course->lessons()->with('media')->orderBy('id')->get(),
        ]);
    }

    public function create(Course $course): View
    {
        return view('admin.lessons.form', ['course' => $course, 'lesson' => new Lesson]);
    }

    public function store(LessonRequest $request, Course $course): RedirectResponse
    {
        $lesson = new Lesson($request->validated());
        $lesson->course_id = $course->id;
        $lesson->position = (int) $course->lessons()->max('position') + 1;
        $lesson->is_published = $request->boolean('is_published');
        $lesson->save();

        return redirect()->route('admin.lessons.media', $lesson)
            ->with('status', "{$lesson->title} хичээлийг нэмлээ. Одоо файлуудаа оруулна уу.");
    }

    public function edit(Lesson $lesson): View
    {
        return view('admin.lessons.form', ['course' => $lesson->course, 'lesson' => $lesson]);
    }

    public function update(LessonRequest $request, Lesson $lesson): RedirectResponse
    {
        $lesson->fill($request->validated());
        $lesson->is_published = $request->boolean('is_published');
        $lesson->save();

        return redirect()->route('admin.courses.lessons.index', $lesson->course_id)
            ->with('status', "{$lesson->title} хичээлийг хадгаллаа.");
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $title = $lesson->title;
        $lesson->delete();

        return back()->with('status', "{$title} хичээлийг устгалаа.");
    }

    /**
     * Хичээлийн дарааллыг нэг алхмаар өөрчилнө. Чирж зөөхөөс ялгаатай нь
     * гар болон дэлгэц уншигчаар бүрэн хийж болно.
     */
    public function move(Request $request, Lesson $lesson): RedirectResponse
    {
        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];

        $lessons = $lesson->course->lessons()->orderBy('id')->get()->values();
        $index = $lessons->search(fn (Lesson $item) => $item->id === $lesson->id);
        $target = $direction === 'up' ? $index - 1 : $index + 1;

        if (! $lessons->has($target)) {
            return back();
        }

        $ordered = $lessons->all();
        [$ordered[$index], $ordered[$target]] = [$ordered[$target], $ordered[$index]];

        DB::transaction(function () use ($ordered) {
            foreach ($ordered as $position => $item) {
                $item->update(['position' => $position + 1]);
            }
        });

        return back()->with('status', "{$lesson->title} хичээл одоо ".($target + 1).'-р байрлалд байна.');
    }
}
