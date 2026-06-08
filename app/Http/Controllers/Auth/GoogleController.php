<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleController extends Controller
{
    /**
     * Redirect the user to Google's OAuth consent screen.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google: link an existing account or create one.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('login')
                ->with('error', 'La connexion avec Google a échoué. Veuillez réessayer.');
        }

        // 1. Already linked by google_id, or a local account with the same email.
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            return $this->loginExisting($user, $googleUser);
        }

        // 2. No account yet — only create one if registration is open.
        if (! setting('site.registration_open', true)) {
            return redirect()->route('login')
                ->with('error', 'Les inscriptions sont temporairement fermées.');
        }

        return $this->registerNew($googleUser);
    }

    /**
     * Log in an existing user, linking their Google id on first use.
     */
    private function loginExisting(User $user, \Laravel\Socialite\Contracts\User $googleUser): RedirectResponse
    {
        if (! $user->is_active && ! $user->hasPendingDeletion()) {
            return redirect()->route('login')
                ->with('error', 'Votre compte a été désactivé. Contactez un administrateur.');
        }

        if (! $user->google_id) {
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'avatar' => $user->avatar ?: $googleUser->getAvatar(),
            ])->save();
        }

        // A Google-authenticated email is verified by Google.
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->intended(route('directory.index'));
    }

    /**
     * Create a brand-new member account from the Google profile.
     */
    private function registerNew(\Laravel\Socialite\Contracts\User $googleUser): RedirectResponse
    {
        [$firstName, $lastName] = $this->splitName($googleUser->getName(), $googleUser->getEmail());

        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
            'password' => null,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();
        $user->assignRole('member');

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        // Email is already verified via Google — go straight to profile creation.
        return redirect()->route('profile.create');
    }

    /**
     * Split Google's full name into first / last; fall back to the email local-part.
     *
     * @return array{0: string, 1: string}
     */
    private function splitName(?string $name, string $email): array
    {
        $name = trim((string) $name);

        if ($name === '') {
            $name = Str::before($email, '@');
        }

        $parts = preg_split('/\s+/', $name, 2) ?: [''];

        return [
            Str::limit($parts[0] ?? '', 100, ''),
            Str::limit($parts[1] ?? '', 100, ''),
        ];
    }
}
