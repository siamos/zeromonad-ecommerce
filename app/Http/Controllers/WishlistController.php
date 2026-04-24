<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'wishable_type' => 'nullable|string|in:product,activity,accommodation,vehicle',
            'wishable_id' => 'nullable|integer',
            'product_id' => 'nullable|exists:products,id',
        ]);

        $user = $request->user();

        if ($request->wishable_type && $request->wishable_id) {
            $wishableType = $request->wishable_type;
            $wishableId = $request->integer('wishable_id');
        } else {
            $wishableType = 'product';
            $wishableId = $request->integer('product_id');
        }

        $existing = Wishlist::where('user_id', $user->id)
            ->where('wishable_type', $wishableType)
            ->where('wishable_id', $wishableId)
            ->first();

        if ($existing) {
            $existing->delete();
            $wishlisted = false;
        } else {
            $data = [
                'user_id' => $user->id,
                'wishable_type' => $wishableType,
                'wishable_id' => $wishableId,
            ];

            if ($wishableType === 'product') {
                $data['product_id'] = $wishableId;
            }

            Wishlist::create($data);
            $wishlisted = true;
        }

        return response()->json(['wishlisted' => $wishlisted]);
    }
}
