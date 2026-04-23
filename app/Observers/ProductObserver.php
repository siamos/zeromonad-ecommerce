<?php

namespace App\Observers;

use App\Jobs\NotifyStockAlert;
use App\Models\Product;

class ProductObserver
{
    public function updating(Product $product): void
    {
        if ($product->isDirty('stock') && $product->getOriginal('stock') <= 0 && $product->stock > 0) {
            $product->_stockRestored = true;
        }
    }

    public function updated(Product $product): void
    {
        if ($product->_stockRestored ?? false) {
            NotifyStockAlert::dispatch($product);
        }
    }
}
