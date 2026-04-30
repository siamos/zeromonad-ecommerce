<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class Vehicle extends Model implements HasMedia
{
    use HasSlug, HasTranslations, InteractsWithMedia, Searchable, SoftDeletes;

    public array $translatable = ['title', 'short_description', 'description'];

    protected $appends = ['image_url', 'name', 'is_on_sale'];

    protected $fillable = [
        'category_id',
        'make',
        'model',
        'year',
        'slug',
        'short_description',
        'description',
        'price_per_day',
        'compare_price',
        'sale_price',
        'sale_starts_at',
        'sale_ends_at',
        'vehicle_type',
        'transmission',
        'seats',
        'mileage_policy',
        'fuel_policy',
        'pickup_location',
        'is_available',
        'extras',
        'status',
        'featured',
    ];

    protected function casts(): array
    {
        return [
            'price_per_day' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'sale_starts_at' => 'datetime',
            'sale_ends_at' => 'datetime',
            'year' => 'integer',
            'seats' => 'integer',
            'is_available' => 'boolean',
            'extras' => 'array',
            'featured' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn ($model) => $model->make.' '.$model->model.' '.$model->year)
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('vehicle-images')
            ->useFallbackUrl('/images/product-placeholder.svg');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(400)
            ->sharpen(10);

        $this->addMediaConversion('medium')
            ->width(800)
            ->height(800);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function orderItems(): MorphMany
    {
        return $this->morphMany(OrderItem::class, 'orderable');
    }

    public function priceTiers(): MorphMany
    {
        return $this->morphMany(PriceTier::class, 'tierable')->orderBy('min_quantity');
    }

    public function priceForQuantity(int $quantity): float
    {
        $tier = $this->priceTiers
            ->filter(fn ($t) => $t->min_quantity <= $quantity)
            ->sortByDesc('min_quantity')
            ->first();

        return $tier ? (float) $tier->price : (float) $this->price_per_day;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $locale = app()->getLocale();

        foreach ($this->translatable as $key) {
            if (isset($array[$key])) {
                $array[$key] = $this->getTranslation($key, $locale, useFallbackLocale: true);
            }
        }

        return $array;
    }

    public function getNameAttribute(): string
    {
        return "{$this->make} {$this->model} {$this->year}";
    }

    public function getImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('vehicle-images') ?: '/images/product-placeholder.svg';
    }

    public function scopeRecommended(Builder $query, self $current, int $limit = 4): Builder
    {
        return $query->where('status', 'published')
            ->where('id', '!=', $current->id)
            ->where('vehicle_type', $current->vehicle_type)
            ->withCount('reviews')
            ->orderByDesc('reviews_count')
            ->orderByDesc('featured')
            ->limit($limit);
    }

    public function toSearchableArray(): array
    {
        return [
            'title' => $this->make.' '.$this->model.' '.$this->year,
            'short_description' => collect($this->getTranslations('short_description'))->values()->implode(' '),
            'make' => $this->make,
            'model' => $this->model,
            'pickup_location' => $this->pickup_location,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === 'published';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function getIsOnSaleAttribute(): bool
    {
        if (! $this->sale_price) {
            return false;
        }
        $now = now();

        return (! $this->sale_starts_at || $this->sale_starts_at <= $now)
            && (! $this->sale_ends_at || $this->sale_ends_at >= $now);
    }

    public function isOnSale(): bool
    {
        return $this->getIsOnSaleAttribute();
    }

    public function scopeOnSale(Builder $query): Builder
    {
        return $query->whereNotNull('sale_price')
            ->where(fn ($q) => $q->whereNull('sale_starts_at')->orWhere('sale_starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('sale_ends_at')->orWhere('sale_ends_at', '>=', now()));
    }
}
