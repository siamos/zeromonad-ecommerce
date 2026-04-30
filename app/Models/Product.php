<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Tags\HasTags;
use Spatie\Translatable\HasTranslations;

class Product extends Model implements HasMedia
{
    use HasFactory, HasSlug, HasTags, HasTranslations, InteractsWithMedia, Searchable, SoftDeletes;

    public array $translatable = ['name', 'description', 'short_description'];

    protected $appends = ['image_url', 'in_stock', 'is_on_sale'];

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'compare_price',
        'sale_price',
        'sale_starts_at',
        'sale_ends_at',
        'stock',
        'sku',
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
            'stock' => 'integer',
            'featured' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn ($model) => $model->getTranslation('name', 'en'))
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product-images')
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

    public function activityDetail(): HasOne
    {
        return $this->hasOne(ActivityDetail::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function activitySlots(): HasMany
    {
        return $this->hasMany(ActivitySlot::class);
    }

    public function priceTiers(): MorphMany
    {
        return $this->morphMany(PriceTier::class, 'tierable')->orderBy('min_quantity');
    }

    public function waitlistEntries(): MorphMany
    {
        return $this->morphMany(Waitlist::class, 'waitlistable');
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

    public function getImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('product-images') ?: '/images/product-placeholder.svg';
    }

    public function getInStockAttribute(): bool
    {
        return $this->stock > 0;
    }

    public function scopeRecommended($query, self $current, int $limit = 4)
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
            'name' => collect($this->getTranslations('name'))->values()->implode(' '),
            'short_description' => collect($this->getTranslations('short_description'))->values()->implode(' '),
            'sku' => $this->sku,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->isPublished();
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
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

    public function scopeOnSale($query)
    {
        return $query->whereNotNull('sale_price')
            ->where(fn ($q) => $q->whereNull('sale_starts_at')->orWhere('sale_starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('sale_ends_at')->orWhere('sale_ends_at', '>=', now()));
    }
}
