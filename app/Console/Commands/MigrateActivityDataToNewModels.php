<?php

namespace App\Console\Commands;

use App\Models\Accommodation;
use App\Models\Activity;
use App\Models\ActivityDetail;
use App\Models\ActivitySlot;
use App\Models\CartItem;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Vehicle;
use App\Models\Wishlist;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MigrateActivityDataToNewModels extends Command
{
    protected $signature = 'app:migrate-activity-data-to-new-models
                            {--dry-run : Preview what would be migrated without making changes}
                            {--type= : Only migrate a specific booking type (activity|accommodation|vehicle)}';

    protected $description = 'Migrate Product+ActivityDetail records to Activity, Accommodation, or Vehicle models. Safe to re-run (idempotent via slug).';

    /** @var array<string,int> */
    private array $counts = ['activity' => 0, 'accommodation' => 0, 'vehicle' => 0, 'skipped' => 0];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $filterType = $this->option('type');

        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }

        $products = Product::with(['activityDetail', 'activitySlots'])
            ->whereHas('activityDetail')
            ->get();

        if ($products->isEmpty()) {
            $this->info('No products with activity_details found. Nothing to migrate.');

            return self::SUCCESS;
        }

        $this->info("Found {$products->count()} product(s) with activity_detail records.");
        $this->newLine();

        foreach ($products as $product) {
            $detail = $product->activityDetail;
            $domainType = $this->resolveDomainType($detail->booking_type ?? 'activity');

            if ($filterType && $domainType !== $filterType) {
                continue;
            }

            $name = $product->getTranslation('name', 'en', useFallbackLocale: true);
            $this->line("  [{$domainType}] #{$product->id} — {$name}");

            match ($domainType) {
                'activity' => $this->migrateToActivity($product, $detail, $dryRun),
                'accommodation' => $this->migrateToAccommodation($product, $detail, $dryRun),
                'vehicle' => $this->migrateToVehicle($product, $detail, $dryRun),
            };
        }

        $this->newLine();
        $this->table(
            ['Type', 'Migrated', 'Skipped (already exists)'],
            [
                ['activity', $this->counts['activity'], '—'],
                ['accommodation', $this->counts['accommodation'], '—'],
                ['vehicle', $this->counts['vehicle'], '—'],
                ['skipped', '—', $this->counts['skipped']],
            ]
        );

        return self::SUCCESS;
    }

    private function resolveDomainType(string $bookingType): string
    {
        return match ($bookingType) {
            'accommodation' => 'accommodation',
            'vehicle' => 'vehicle',
            default => 'activity',
        };
    }

    private function migrateToActivity(Product $product, ActivityDetail $detail, bool $dryRun): void
    {
        $slug = $product->slug;

        if (Activity::where('slug', $slug)->exists()) {
            $this->warn("    → skipping (Activity slug '{$slug}' already exists)");
            $this->counts['skipped']++;

            return;
        }

        if ($dryRun) {
            $this->info("    → would create Activity (slug: {$slug})");

            return;
        }

        $titles = $product->getTranslations('name');
        $descriptions = $product->getTranslations('description');
        $shortDescriptions = $product->getTranslations('short_description');

        $activity = Activity::create([
            'category_id' => $product->category_id,
            'title' => $titles,
            'slug' => $slug,
            'short_description' => $shortDescriptions,
            'description' => $descriptions,
            'price' => $product->price,
            'compare_price' => $product->compare_price,
            'location' => $detail->location,
            'duration_minutes' => $detail->duration_minutes,
            'max_participants' => $detail->capacity,
            'booking_cutoff_hours' => $detail->booking_cutoff_hours ?? 24,
            'status' => $product->status,
            'featured' => $product->featured,
        ]);

        $this->transferMedia($product, $activity, 'product-images', 'activity-images', 'activity');
        $this->transferRelations($product->id, 'product', $activity->id, 'activity');
        $this->transferActivitySlots($product->id, $activity->id);

        $this->counts['activity']++;
        $this->info("    → created Activity #{$activity->id}");
    }

    private function migrateToAccommodation(Product $product, ActivityDetail $detail, bool $dryRun): void
    {
        $slug = $product->slug;

        if (Accommodation::where('slug', $slug)->exists()) {
            $this->warn("    → skipping (Accommodation slug '{$slug}' already exists)");
            $this->counts['skipped']++;

            return;
        }

        if ($dryRun) {
            $this->info("    → would create Accommodation (slug: {$slug})");

            return;
        }

        $extra = $detail->extra_attributes ?? [];

        $accommodation = Accommodation::create([
            'category_id' => $product->category_id,
            'title' => $product->getTranslations('name'),
            'slug' => $slug,
            'short_description' => $product->getTranslations('short_description'),
            'description' => $product->getTranslations('description'),
            'price_per_night' => $product->price,
            'compare_price' => $product->compare_price,
            'location' => $detail->location ?? ($extra['location'] ?? null),
            'bedrooms' => $extra['bedrooms'] ?? 1,
            'bathrooms' => $extra['bathrooms'] ?? 1,
            'max_guests' => $extra['max_guests'] ?? 2,
            'amenities' => $extra['amenities'] ?? null,
            'house_rules' => $extra['house_rules'] ?? null,
            'status' => $product->status,
            'featured' => $product->featured,
        ]);

        $this->transferMedia($product, $accommodation, 'product-images', 'accommodation-images', 'accommodation');
        $this->transferRelations($product->id, 'product', $accommodation->id, 'accommodation');

        $this->counts['accommodation']++;
        $this->info("    → created Accommodation #{$accommodation->id}");
    }

    private function migrateToVehicle(Product $product, ActivityDetail $detail, bool $dryRun): void
    {
        $slug = $product->slug;

        if (Vehicle::where('slug', $slug)->exists()) {
            $this->warn("    → skipping (Vehicle slug '{$slug}' already exists)");
            $this->counts['skipped']++;

            return;
        }

        if ($dryRun) {
            $this->info("    → would create Vehicle (slug: {$slug})");

            return;
        }

        $extra = $detail->extra_attributes ?? [];
        $nameEn = $product->getTranslation('name', 'en', useFallbackLocale: true);

        $vehicle = Vehicle::create([
            'category_id' => $product->category_id,
            'make' => $extra['make'] ?? $nameEn,
            'model' => $extra['model'] ?? 'Unknown',
            'year' => (int) ($extra['year'] ?? now()->year),
            'slug' => $slug,
            'short_description' => $product->getTranslations('short_description'),
            'description' => $product->getTranslations('description'),
            'price_per_day' => $product->price,
            'compare_price' => $product->compare_price,
            'vehicle_type' => $extra['vehicle_type'] ?? 'car',
            'transmission' => $extra['transmission'] ?? 'manual',
            'seats' => (int) ($extra['seats'] ?? 5),
            'mileage_policy' => $extra['mileage_policy'] ?? null,
            'fuel_policy' => $extra['fuel_policy'] ?? null,
            'pickup_location' => $extra['pickup_location'] ?? $detail->location,
            'is_available' => true,
            'extras' => $extra['available_extras'] ?? null,
            'status' => $product->status,
            'featured' => $product->featured,
        ]);

        $this->transferMedia($product, $vehicle, 'product-images', 'vehicle-images', 'vehicle');
        $this->transferRelations($product->id, 'product', $vehicle->id, 'vehicle');

        $this->counts['vehicle']++;
        $this->info("    → created Vehicle #{$vehicle->id}");
    }

    private function transferMedia(
        Product $product,
        Model $newModel,
        string $fromCollection,
        string $toCollection,
        string $newMorphAlias
    ): void {
        DB::table('media')
            ->where('model_type', 'product')
            ->where('model_id', $product->id)
            ->where('collection_name', $fromCollection)
            ->update([
                'model_type' => $newMorphAlias,
                'model_id' => $newModel->id,
                'collection_name' => $toCollection,
            ]);
    }

    private function transferRelations(
        int $productId,
        string $fromMorphAlias,
        int $newId,
        string $toMorphAlias
    ): void {
        Review::where('product_id', $productId)
            ->whereNull('reviewable_type')
            ->update([
                'reviewable_type' => $toMorphAlias,
                'reviewable_id' => $newId,
            ]);

        OrderItem::where('product_id', $productId)
            ->whereNull('orderable_type')
            ->update([
                'orderable_type' => $toMorphAlias,
                'orderable_id' => $newId,
            ]);

        CartItem::where('product_id', $productId)
            ->whereNull('cartable_type')
            ->update([
                'cartable_type' => $toMorphAlias,
                'cartable_id' => $newId,
            ]);

        Wishlist::where('product_id', $productId)
            ->whereNull('wishable_type')
            ->update([
                'wishable_type' => $toMorphAlias,
                'wishable_id' => $newId,
            ]);
    }

    private function transferActivitySlots(int $productId, int $activityId): void
    {
        ActivitySlot::where('product_id', $productId)
            ->whereNull('activity_id')
            ->update(['activity_id' => $activityId]);
    }
}
