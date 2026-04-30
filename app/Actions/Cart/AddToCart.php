<?php

namespace App\Actions\Cart;

use App\Models\Accommodation;
use App\Models\Bundle;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\RentalLocation;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class AddToCart
{
    use AsAction;

    public function handleBundle(Bundle $bundle, ?int $userId = null, ?string $sessionId = null): void
    {
        $bundle->load('items.product');

        foreach ($bundle->items as $bundleItem) {
            $this->handle(
                bookable: $bundleItem->product,
                quantity: $bundleItem->quantity,
                options: ['bundle_id' => $bundle->id, 'bundle_name' => $bundle->name],
                userId: $userId,
                sessionId: $sessionId,
            );
        }
    }

    public function handle(Model $bookable, int $quantity = 1, array $options = [], ?int $userId = null, ?string $sessionId = null): CartItem
    {
        $cart = Cart::firstOrCreate(
            $userId
                ? ['user_id' => $userId]
                : ['session_id' => $sessionId],
        );

        $morphMap = Relation::morphMap();
        $morphType = array_search($bookable::class, $morphMap) ?: $bookable::class;

        $item = $cart->items()
            ->where('cartable_type', $morphType)
            ->where('cartable_id', $bookable->id)
            ->first();

        if ($item) {
            $newQuantity = $item->quantity + $quantity;
            $item->increment('quantity', $quantity);
            $newPrice = $this->resolvePrice($bookable, $newQuantity);
            if ($newPrice !== (float) $item->unit_price) {
                $item->update(['unit_price' => $newPrice]);
            }
        } else {
            $itemData = [
                'cartable_type' => $morphType,
                'cartable_id' => $bookable->id,
                'quantity' => $quantity,
                'unit_price' => $this->resolvePrice($bookable, $quantity),
                'options' => $options,
            ];

            if ($bookable instanceof Product) {
                $itemData['product_id'] = $bookable->id;
            }

            $item = $cart->items()->create($itemData);
        }

        return $item->fresh();
    }

    private function resolvePrice(Model $bookable, int $quantity = 1): float
    {
        if (method_exists($bookable, 'priceTiers') && $bookable->priceTiers->isNotEmpty()) {
            $tier = $bookable->priceTiers
                ->filter(fn ($t) => $t->min_quantity <= $quantity)
                ->sortByDesc('min_quantity')
                ->first();

            if ($tier) {
                return (float) $tier->price;
            }
        }

        if (method_exists($bookable, 'getIsOnSaleAttribute') && $bookable->is_on_sale && $bookable->sale_price) {
            return (float) $bookable->sale_price;
        }

        return match (true) {
            $bookable instanceof Accommodation => (float) $bookable->price_per_night,
            $bookable instanceof Vehicle => (float) $bookable->price_per_day,
            default => (float) $bookable->price,
        };
    }

    private function resolveName(Model $bookable): string
    {
        if ($bookable instanceof Vehicle) {
            return "{$bookable->make} {$bookable->model} {$bookable->year}";
        }

        if ($bookable instanceof Product) {
            $name = $bookable->name;

            return is_array($name) ? ($name['en'] ?? '') : (string) $name;
        }

        $title = $bookable->title ?? '';

        return is_array($title) ? ($title['en'] ?? '') : (string) $title;
    }

    public function asController(Request $request): RedirectResponse
    {
        $request->validate([
            'bookable_type' => 'nullable|string|in:product,activity,accommodation,vehicle',
            'bookable_id' => 'nullable|integer',
            'bundle_id' => 'nullable|exists:bundles,id',
            'product_id' => 'nullable|exists:products,id',
            'quantity' => 'integer|min:1|max:99',
            'booking_date' => 'nullable|date',
            'check_in' => 'nullable|date',
            'check_out' => 'nullable|date|after:check_in',
            'pickup_date' => 'nullable|date',
            'return_date' => 'nullable|date|after:pickup_date',
            'guests' => 'nullable|integer|min:1',
            'slot_id' => 'nullable|exists:activity_slots,id',
            'extras' => 'nullable|array',
            'extras.*' => 'string',
            'pickup_location_id' => 'nullable|exists:rental_locations,id',
            'dropoff_location_id' => 'nullable|exists:rental_locations,id',
        ]);

        if ($request->bundle_id) {
            $bundle = Bundle::findOrFail($request->bundle_id);
            $this->handleBundle(
                bundle: $bundle,
                userId: $request->user()?->id,
                sessionId: $request->session()->getId(),
            );

            return back()->with('success', 'Bundle "'.$bundle->name.'" added to cart.');
        }

        if ($request->bookable_type && $request->bookable_id) {
            $modelClass = Relation::getMorphedModel($request->bookable_type);
            $bookable = $modelClass::findOrFail($request->bookable_id);
        } else {
            $bookable = Product::findOrFail($request->product_id);
        }

        $pickupLocation = $request->pickup_location_id
            ? RentalLocation::find($request->pickup_location_id)
            : null;

        $dropoffLocation = $request->dropoff_location_id
            ? RentalLocation::find($request->dropoff_location_id)
            : null;

        $options = array_filter([
            'booking_date' => $request->booking_date,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'pickup_date' => $request->pickup_date,
            'return_date' => $request->return_date,
            'guests' => $request->guests,
            'slot_id' => $request->slot_id,
            'extras' => $request->extras ?: null,
            'pickup_location_id' => $request->pickup_location_id ?: null,
            'pickup_location_name' => $pickupLocation?->name,
            'pickup_fee' => $pickupLocation ? (float) $pickupLocation->pickup_fee : null,
            'dropoff_location_id' => $request->dropoff_location_id ?: null,
            'dropoff_location_name' => $dropoffLocation?->name,
            'dropoff_fee' => $dropoffLocation ? (float) $dropoffLocation->dropoff_fee : null,
        ]);

        $this->handle(
            bookable: $bookable,
            quantity: $request->integer('quantity', 1),
            options: $options,
            userId: $request->user()?->id,
            sessionId: $request->session()->getId(),
        );

        return back()->with('success', 'Added '.$this->resolveName($bookable).' to cart.');
    }
}
