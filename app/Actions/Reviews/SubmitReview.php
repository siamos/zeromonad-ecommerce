<?php

namespace App\Actions\Reviews;

use App\Http\Requests\ReviewRequest;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class SubmitReview
{
    use AsAction;

    public function handle(int $productId, int $userId, int $rating, string $body, ?string $title = null): Review
    {
        return Review::updateOrCreate(
            ['product_id' => $productId, 'user_id' => $userId],
            ['rating' => $rating, 'title' => $title, 'body' => $body, 'status' => 'pending'],
        );
    }

    public function asController(ReviewRequest $request): RedirectResponse
    {
        $this->handle(
            productId: $request->integer('product_id'),
            userId: $request->user()->id,
            rating: $request->integer('rating'),
            body: $request->string('body'),
            title: $request->input('title'),
        );

        return back()->with('success', 'Your review has been submitted and is awaiting approval.');
    }
}
