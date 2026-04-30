<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PriceTier extends Model
{
    protected $fillable = [
        'tierable_type',
        'tierable_id',
        'min_quantity',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'min_quantity' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function tierable(): MorphTo
    {
        return $this->morphTo();
    }
}
