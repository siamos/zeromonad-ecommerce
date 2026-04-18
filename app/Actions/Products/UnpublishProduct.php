<?php

namespace App\Actions\Products;

use App\Models\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class UnpublishProduct
{
    use AsAction;

    public function handle(Product $product): Product
    {
        $product->update(['status' => 'draft']);

        return $product;
    }
}
