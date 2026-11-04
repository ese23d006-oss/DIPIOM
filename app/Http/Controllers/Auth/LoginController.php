<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();
        $this->discardIntendedUrlOutsideRole($request, $user);

        return redirect()->intended($user->dashboardUrl());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Та системээс гарлаа.');
    }

    /**
     * Өөр эрхийн хэсэг рүү орох гэж байсан бол нэвтэрсний дараа 403 харуулахгүй,
     * өөрийн dashboard руу шилжүүлнэ.
     */
    private function discardIntendedUrlOutsideRole(Request $request, User $user): void
    {
        $intended = (string) $request->session()->get('url.intended', '');
        $path = trim((string) parse_url($intended, PHP_URL_PATH), '/');
        $isAdminPath = $path === 'admin' || str_starts_with($path, 'admin/');

        if ($isAdminPath !== $user->isAdmin()) {
            $request->session()->forget('url.intended');
        }
    }
}
