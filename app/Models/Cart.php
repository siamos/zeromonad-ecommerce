<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected $appends = ['subtotal', 'discount_amount', 'discount', 'total', 'item_count'];

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

    protected function subtotal(): Attribute
    {
        return Attribute::get(
            fn () => (float) $this->items->sum(fn ($item) => $item->unit_price * $item->quantity)
        );
    }

    protected function discount(): Attribute
    {
        return Attribute::get(
            fn () => (float) ($this->coupon?->calculateDiscount($this->subtotal) ?? 0)
        );
    }

    protected function discountAmount(): Attribute
    {
        return Attribute::get(fn () => $this->discount);
    }

    protected function total(): Attribute
    {
        return Attribute::get(
            fn () => (float) max(0, $this->subtotal - $this->discount_amount)
        );
    }

    protected function itemCount(): Attribute
    {
        return Attribute::get(
            fn () => (int) $this->items->sum('quantity')
        );
    }
}
