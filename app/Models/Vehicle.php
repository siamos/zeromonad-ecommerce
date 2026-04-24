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

    protected $appends = ['image_url', 'name'];

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
}
