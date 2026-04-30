<?php

namespace App\Actions\Referrals;

use App\Models\Referral;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class AwardReferral
{
    use AsAction;

    public const REWARD_POINTS = 100;

    public function handle(User $referredUser, string $code): void
    {
        $referrer = User::where('referral_code', $code)->first();

        if (! $referrer || $referrer->id === $referredUser->id) {
            return;
        }

        $alreadyRewarded = Referral::where('referrer_user_id', $referrer->id)
            ->where('referred_user_id', $referredUser->id)
            ->exists();

        if ($alreadyRewarded) {
            return;
        }

        Referral::create([
            'referrer_user_id' => $referrer->id,
            'referred_user_id' => $referredUser->id,
            'code' => $code,
            'reward_points' => self::REWARD_POINTS,
            'rewarded_at' => now(),
        ]);

        $referrer->awardPoints(self::REWARD_POINTS, "Referral reward — {$referredUser->name} signed up");
    }
}
