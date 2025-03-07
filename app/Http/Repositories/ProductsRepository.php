<?php

namespace App\Http\Repositories;

use App\Http\Contracts\ProductsInterface;
use App\Models\Product;

class ProductsRepository implements ProductsInterface
{
    public function __construct(protected Product $product){}

    public function getById(int $id): Product
    {
        return $this->product->find($id);
    }

}
