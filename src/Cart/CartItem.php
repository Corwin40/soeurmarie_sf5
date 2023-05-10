<?php

namespace App\Cart;

use App\Entity\Gestapp\Product;
use App\Entity\Gestapp\ProductCustomize;

class CartItem
{
    public $product;
    public $qty;
    public $item;
    public $productCustomize;

    public function __construct(Product $product, int $qty, int $item, ProductCustomize $productCustomize)
    {
        $this->product = $product;
        $this->qty = $qty;
        $this->item = $item;
        $this->productCustomize = $productCustomize;

    }

    public function getTotal()
    {
        return $this->product->getPrice() * $this->qty;
    }
}