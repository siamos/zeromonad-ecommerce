<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RentalLocation extends Model
{
    protected $fillable = [
        'name',
        'address',
        'type',
        'pickup_available',
        'dropoff_available',
        'pickup_fee',
        'dropoff_fee',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'pickup_available' => 'boolean',
            'dropoff_available' => 'boolean',
            'pickup_fee' => 'decimal:2',
            'dropoff_fee' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    public function scopeForPickup(Builder $query): Builder
    {
        return $query->active()->where('pickup_available', true);
    }

    public function scopeForDropoff(Builder $query): Builder
    {
        return $query->active()->where('dropoff_available', true);
    }
}
