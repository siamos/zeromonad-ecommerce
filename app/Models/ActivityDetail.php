<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityDetail extends Model
{
    protected $fillable = [
        'product_id',
        'event_date',
        'location',
        'capacity',
        'duration_minutes',
        'booking_cutoff_hours',
    ];

    protected function casts(): array
    {
        return [
            'event_date'           => 'datetime',
            'capacity'             => 'integer',
            'duration_minutes'     => 'integer',
            'booking_cutoff_hours' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isFull(): bool
    {
        return $this->product->orderItems()->count() >= $this->capacity;
    }

    public function isBookingOpen(): bool
    {
        return now()->addHours($this->booking_cutoff_hours)->lt($this->event_date);
    }
}
