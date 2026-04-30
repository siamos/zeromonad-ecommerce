<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class Activity extends Model implements HasMedia
{
    use HasSlug, HasTranslations, InteractsWithMedia, Searchable, SoftDeletes;

    public array $translatable = ['title', 'description', 'short_description'];

    protected $appends = ['image_url', 'name', 'is_on_sale'];

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'short_description',
        'description',
        'price',
        'compare_price',
        'sale_price',
        'sale_starts_at',
        'sale_ends_at',
        'location',
        'duration_minutes',
        'max_participants',
        'min_participants',
        'price_per_person',
        'booking_cutoff_hours',
        'difficulty',
        'min_age',
        'weather_dependent',
        'cancellation_policy',
        'status',
        'featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'sale_starts_at' => 'datetime',
            'sale_ends_at' => 'datetime',
            'duration_minutes' => 'integer',
            'max_participants' => 'integer',
            'min_participants' => 'integer',
            'price_per_person' => 'decimal:2',
            'booking_cutoff_hours' => 'integer',
            'min_age' => 'integer',
            'weather_dependent' => 'boolean',
            'featured' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn ($model) => $model->getTranslation('title', 'en'))
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('activity-images')
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

    public function slots(): HasMany
    {
        return $this->hasMany(ActivitySlot::class);
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

        return $tier ? (float) $tier->price : (float) $this->price;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $locale = app()->getLocale();

        foreach ($this->translatable as $key) {
            $array[$key] = $this->getTranslation($key, $locale, useFallbackLocale: true);
        }

        return $array;
    }

    public function getNameAttribute(): string
    {
        return $this->getTranslation('title', app()->getLocale(), useFallbackLocale: true);
    }

    public function getImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('activity-images') ?: '/images/product-placeholder.svg';
    }

    public function scopeRecommended(Builder $query, self $current, int $limit = 4): Builder
    {
        return $query->where('status', 'published')
            ->where('id', '!=', $current->id)
            ->where('category_id', $current->category_id)
            ->withCount('reviews')
            ->orderByDesc('reviews_count')
            ->orderByDesc('featured')
            ->limit($limit);
    }

    public function toSearchableArray(): array
    {
        return [
            'title' => collect($this->getTranslations('title'))->values()->implode(' '),
            'short_description' => collect($this->getTranslations('short_description'))->values()->implode(' '),
            'location' => $this->location,
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
