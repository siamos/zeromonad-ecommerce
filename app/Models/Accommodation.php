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

class Accommodation extends Model implements HasMedia
{
    use HasSlug, HasTranslations, InteractsWithMedia, Searchable, SoftDeletes;

    public array $translatable = ['title', 'description', 'short_description'];

    protected $appends = ['image_url', 'name'];

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'short_description',
        'description',
        'price_per_night',
        'compare_price',
        'location',
        'bedrooms',
        'bathrooms',
        'max_guests',
        'amenities',
        'house_rules',
        'status',
        'featured',
    ];

    protected function casts(): array
    {
        return [
            'price_per_night' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'max_guests' => 'integer',
            'amenities' => 'array',
            'house_rules' => 'array',
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
        $this->addMediaCollection('accommodation-images')
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

    public function blockedDates(): HasMany
    {
        return $this->hasMany(AccommodationBlockedDate::class);
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function orderItems(): MorphMany
    {
        return $this->morphMany(OrderItem::class, 'orderable');
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
        return $this->getFirstMediaUrl('accommodation-images') ?: '/images/product-placeholder.svg';
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

    public function isAvailableForDates(string $checkIn, string $checkOut): bool
    {
        return ! $this->blockedDates()
            ->where('start_date', '<=', $checkOut)
            ->where('end_date', '>=', $checkIn)
            ->exists();
    }
}
