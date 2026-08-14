<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect($this->pathFor($request, 'dashboard'));
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            return back()->withErrors(['username' => 'Invalid credentials.'])->onlyInput('username');
        }

        $request->session()->regenerate();

        return redirect()->intended($this->pathFor($request, 'dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $loginPath = $this->pathFor($request, 'login');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($loginPath);
    }

    /**
     * Builds a same-style URL for the given tail path: keeps the {slug}
     * prefix when the request was reached via salonflow.test/{slug}/..., or
     * uses the bare subdomain host otherwise.
     */
    private function pathFor(Request $request, string $tail): string
    {
        $slugPrefix = $request->attributes->get('tenant_slug_prefix');

        if ($slugPrefix) {
            return $request->getSchemeAndHttpHost()."/{$slugPrefix}/{$tail}";
        }

        return $request->getSchemeAndHttpHost()."/{$tail}";
    }
}
