<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        abort_unless($provider === 'google', 404);

        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_unless($provider === 'google', 404);

        $socialUser = Socialite::driver($provider)->stateless()->user();

        $idColumn = "{$provider}_id";

        $user = User::where($idColumn, $socialUser->getId())->first()
            ?? User::where('email', $socialUser->getEmail())->first()
            ?? new User;

        $user->fill([
            'name' => $user->exists ? $user->name : ($socialUser->getName() ?? $socialUser->getNickname() ?? 'User'),
            'email' => $socialUser->getEmail(),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        $user->{$idColumn} = $socialUser->getId();
        $user->save();

        Auth::login($user, remember: true);

        return redirect()->intended(route('home'));
    }
}
