<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MemberController extends Controller
{
    public function home(): View
    {
        $user = Auth::guard('member')->user();

        if ($user) {
            abort_unless($user->role === User::ROLE_STUDENT && $user->status === 'active', 403);

            return view('member.home', ['user' => $user]);
        }

        return view('signin');
    }

    public function login(Request $request): RedirectResponse
    {
        $guard = Auth::guard('member');
        $authenticatedUser = $guard->user();

        if ($authenticatedUser) {
            abort_unless(
                $authenticatedUser->role === User::ROLE_STUDENT && $authenticatedUser->status === 'active',
                403,
            );

            return redirect()->route('member.home');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $user = User::query()
            ->where('email', $credentials['email'])
            ->where('role', User::ROLE_STUDENT)
            ->where('status', 'active')
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak sesuai.',
            ]);
        }

        $guard->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('member.home');
    }

    public function profile(): View
    {
        return view('member.profile', ['user' => Auth::guard('member')->user()]);
    }

    public function photo(): BinaryFileResponse
    {
        $path = Auth::guard('member')->user()->child_photo;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $passwords = $request->validate([
            'current_password' => ['required', 'current_password:member'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);
        $user = Auth::guard('member')->user();
        $user->password = $passwords['new_password'];
        $user->setRememberToken(Str::random(60));
        $user->save();
        $request->session()->regenerate();

        return redirect()
            ->route('member.profile')
            ->with('status', 'Kata sandi berhasil diubah.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('member')->logout();
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('member.home')
            ->with('status', 'Anda berhasil keluar.');
    }
}
