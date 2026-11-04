<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyCourseController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('student.my-courses', [
            'courses' => Course::startedBy($request->user())->with('category')->paginate(12),
        ]);
    }
}
