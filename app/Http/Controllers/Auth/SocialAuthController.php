<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Referrals\AwardReferral;
use App\Http\Controllers\Controller;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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

        $isNewUser = ! $user->exists;
        $isFirstLink = $user->exists && empty($user->{$idColumn});

        $user->fill([
            'name' => $user->exists ? $user->name : ($socialUser->getName() ?? $socialUser->getNickname() ?? 'User'),
            'email' => $socialUser->getEmail(),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        if ($isNewUser) {
            $user->referral_code = User::generateReferralCode();
        }

        $user->{$idColumn} = $socialUser->getId();
        $user->save();

        if ($isNewUser) {
            $user->assignRole('customer');

            if ($refCode = session()->pull('referral_code')) {
                AwardReferral::run($user, $refCode);
            }
        }

        if ($isNewUser || $isFirstLink) {
            Mail::to($user->email)->queue(new WelcomeEmail($user));
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('home'));
    }
}
