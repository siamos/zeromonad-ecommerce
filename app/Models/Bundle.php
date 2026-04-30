<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bundle extends Model
{
    protected $fillable = ['name', 'description', 'price', 'is_active', 'theme'];

    public function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(BundleItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForTheme(Builder $query, string $theme): Builder
    {
        return $query->where(function (Builder $q) use ($theme) {
            $q->where('theme', $theme)->orWhereNull('theme');
        });
    }
}
