<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SharedLoginController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof User && $user->is_active) {
            $panelUrl = $this->panelUrl($user);

            if ($panelUrl !== null) {
                return redirect()->to($panelUrl);
            }
        }

        if ($user !== null) {
            $this->endSession($request);
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $authenticated = Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true,
        ], $request->boolean('remember'));

        if (! $authenticated) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak sesuai, atau akun tidak aktif.',
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();
        $panelUrl = $user instanceof User ? $this->panelUrl($user) : null;

        if ($panelUrl === null) {
            $this->endSession($request);

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak sesuai, atau akun tidak aktif.',
            ]);
        }

        return redirect()->to($panelUrl);
    }

    private function panelUrl(User $user): ?string
    {
        return match ($user->role) {
            UserRole::Manager => url('/app'),
            UserRole::FieldStaff => url('/petugas'),
            UserRole::Citizen => url('/warga'),
            default => null,
        };
    }

    private function endSession(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
