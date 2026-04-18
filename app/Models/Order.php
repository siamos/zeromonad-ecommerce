<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'coupon_id',
        'order_number',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'shipping_amount',
        'total',
        'payment_method',
        'payment_status',
        'payment_intent_id',
        'payment_gateway_transaction_id',
        'payment_verified_at',
        'billing_address',
        'shipping_address',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'             => 'decimal:2',
            'discount_amount'      => 'decimal:2',
            'tax_amount'           => 'decimal:2',
            'shipping_amount'      => 'decimal:2',
            'total'                => 'decimal:2',
            'billing_address'      => 'array',
            'shipping_address'     => 'array',
            'payment_verified_at'  => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'payment_status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

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
        return $this->hasMany(OrderItem::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
        });
    }
}
