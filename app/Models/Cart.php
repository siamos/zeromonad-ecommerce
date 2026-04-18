<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'coupon_id',
        'session_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function subtotal(): float
    {
        return (float) $this->items->sum(fn ($item) => $item->unit_price * $item->quantity);
    }

    public function total(): float
    {
        $subtotal = $this->subtotal();
        $discount = $this->coupon?->calculateDiscount($subtotal) ?? 0;

        return max(0, $subtotal - $discount);
    }

    public function itemCount(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
