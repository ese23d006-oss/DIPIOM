<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View|RedirectResponse
    {
        return SystemSetting::registrationOpen() ? view('auth.register') : $this->closed();
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        if (! SystemSetting::registrationOpen()) {
            return $this->closed();
        }

        // Шинэ хэрэглэгч үргэлж суралцагч эрхтэй үүснэ (User::$fillable-д role байхгүй).
        $user = User::create($request->safe()->only(['name', 'email', 'phone', 'password']));

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($user->dashboardUrl())->with('status', 'Бүртгэл амжилттай үүслээ. Тавтай морил!');
    }

    private function closed(): RedirectResponse
    {
        return redirect()->route('login')->with('error', 'Шинэ бүртгэл одоогоор хаалттай байна. Админтай холбогдоно уу.');
    }
}
