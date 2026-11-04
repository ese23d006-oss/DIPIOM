<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FeedbackStatus;
use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $status = FeedbackStatus::tryFrom(is_string($request->query('status')) ? $request->query('status') : '');

        return view('admin.feedback.index', [
            'items' => Feedback::with('user:id,name')
                ->when($status, fn ($query) => $query->where('status', $status))
                ->latest()
                ->paginate(30)
                ->withQueryString(),
            'status' => $status?->value ?? '',
        ]);
    }

    public function show(Feedback $feedback): View
    {
        return view('admin.feedback.show', ['item' => $feedback->load(['user:id,name', 'replier:id,name'])]);
    }

    public function reply(Request $request, Feedback $feedback): RedirectResponse
    {
        $data = $request->validate(['admin_reply' => ['required', 'string', 'max:5000']]);

        $feedback->forceFill([
            'admin_reply' => $data['admin_reply'],
            'status' => FeedbackStatus::Answered,
            'replied_by' => $request->user()->id,
            'replied_at' => now(),
        ])->save();

        return redirect()->route('admin.feedback.index')->with('status', 'Хариу илгээгдлээ.');
    }
}
