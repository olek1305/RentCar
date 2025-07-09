<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * @return Factory|Application|View
     */
    public function showLogin(): Factory|Application|View
    {
        return view('auth.login');
    }

    /**
     * @param Request $request
     * @return Application|Redirector|RedirectResponse
     */
    public function login(Request $request): Application|Redirector|RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect('/')->with('success', __('messages.login_success'));
        }

        return back()->with('error', __('messages.auth_error'));
    }

    /**
     * @param Request $request
     * @return Application|Redirector|RedirectResponse
     */
    public function logout(Request $request): Application|Redirector|RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', __('messages.logout_success'));
    }
}
