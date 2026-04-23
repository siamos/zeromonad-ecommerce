<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivitySlot extends Model
{
    protected $fillable = [
        'product_id',
        'date',
        'capacity',
        'booked_count',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'capacity' => 'integer',
            'booked_count' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function spotsRemaining(): int
    {
        return max(0, $this->capacity - $this->booked_count);
    }

    public function isFull(): bool
    {
        return $this->spotsRemaining() === 0;
    }
}
