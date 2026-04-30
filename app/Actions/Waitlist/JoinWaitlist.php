<?php

namespace App\Actions\Waitlist;

use App\Models\Waitlist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class JoinWaitlist
{
    use AsAction;

    public function handle(Model $waitlistable, string $email, ?int $userId = null): Waitlist
    {
        $morphMap = Relation::morphMap();
        $morphType = array_search($waitlistable::class, $morphMap) ?: $waitlistable::class;

        return Waitlist::firstOrCreate(
            [
                'email' => $email,
                'waitlistable_type' => $morphType,
                'waitlistable_id' => $waitlistable->id,
            ],
            ['user_id' => $userId],
        );
    }

    public function asController(Request $request): RedirectResponse
    {
        $request->validate([
            'waitlistable_type' => 'required|string',
            'waitlistable_id' => 'required|integer',
            'email' => 'required|email',
        ]);

        $modelClass = Relation::getMorphedModel($request->waitlistable_type) ?? $request->waitlistable_type;
        $waitlistable = $modelClass::findOrFail($request->waitlistable_id);

        $this->handle(
            waitlistable: $waitlistable,
            email: $request->email,
            userId: $request->user()?->id,
        );

        return back()->with('success', 'You have been added to the waitlist. We\'ll notify you when this item is back in stock.');
    }
}
