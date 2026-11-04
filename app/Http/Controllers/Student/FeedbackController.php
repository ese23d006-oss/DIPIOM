<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        return view('student.feedback.index', [
            'items' => $request->user()->feedback()->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $request->user()->feedback()->create($data);

        return redirect()->route('student.feedback')->with('status', 'Санал хүсэлт илгээгдлээ. Хариуг энэ хуудаснаас харна.');
    }
}
