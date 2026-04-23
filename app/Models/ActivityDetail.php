<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityDetail extends Model
{
    protected $fillable = [
        'product_id',
        'booking_type',
        'event_date',
        'location',
        'capacity',
        'duration_minutes',
        'booking_cutoff_hours',
        'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'capacity' => 'integer',
            'duration_minutes' => 'integer',
            'booking_cutoff_hours' => 'integer',
            'extra_attributes' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function bookedCount(): int
    {
        return (int) $this->product->orderItems()
            ->whereHas('order', fn ($q) => $q->whereNotIn('status', ['cancelled']))
            ->sum('quantity');
    }

    public function spotsRemaining(): int
    {
        return max(0, ($this->capacity ?? 0) - $this->bookedCount());
    }

    public function isFull(): bool
    {
        return $this->capacity !== null && $this->spotsRemaining() === 0;
    }

    public function isBookingOpen(): bool
    {
        if (! $this->event_date || ! $this->booking_cutoff_hours) {
            return true;
        }

        return now()->addHours($this->booking_cutoff_hours)->lt($this->event_date);
    }
}
