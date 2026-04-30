<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Referrals\AwardReferral;
use App\Http\Controllers\Controller;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'referral_code' => User::generateReferralCode(),
        ]);

        $user->assignRole('customer');

        event(new Registered($user));

        Mail::to($user->email)->queue(new WelcomeEmail($user));

        Auth::login($user);

        if ($refCode = session()->pull('referral_code')) {
            AwardReferral::run($user, $refCode);
        }

        return redirect(route('home', absolute: false));
    }
}
