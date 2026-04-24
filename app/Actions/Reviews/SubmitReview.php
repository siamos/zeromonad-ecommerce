<?php

namespace App\Actions\Reviews;

use App\Http\Requests\ReviewRequest;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class SubmitReview
{
    use AsAction;

    public function handle(string $reviewableType, int $reviewableId, int $userId, int $rating, string $body, ?string $title = null): Review
    {
        $data = [
            'rating' => $rating,
            'title' => $title,
            'body' => $body,
            'status' => 'pending',
        ];

        if ($reviewableType === 'product') {
            $data['product_id'] = $reviewableId;
        }

        return Review::updateOrCreate(
            ['reviewable_type' => $reviewableType, 'reviewable_id' => $reviewableId, 'user_id' => $userId],
            $data,
        );
    }

    public function asController(ReviewRequest $request): RedirectResponse
    {
        if ($request->reviewable_type && $request->reviewable_id) {
            $reviewableType = $request->reviewable_type;
            $reviewableId = $request->integer('reviewable_id');
        } else {
            $reviewableType = 'product';
            $reviewableId = $request->integer('product_id');
        }

        $this->handle(
            reviewableType: $reviewableType,
            reviewableId: $reviewableId,
            userId: $request->user()->id,
            rating: $request->integer('rating'),
            body: $request->string('body'),
            title: $request->input('title'),
        );

        return back()->with('success', 'Your review has been submitted and is awaiting approval.');
    }
}
