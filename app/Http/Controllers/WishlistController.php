<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function toggle(Request $request): JsonResponse
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $user = $request->user();
        $productId = $request->integer('product_id');

        $existing = Wishlist::where('user_id', $user->id)->where('product_id', $productId)->first();

        if ($existing) {
            $existing->delete();
            $wishlisted = false;
        } else {
            Wishlist::create(['user_id' => $user->id, 'product_id' => $productId]);
            $wishlisted = true;
        }

        return response()->json(['wishlisted' => $wishlisted]);
    }
}
