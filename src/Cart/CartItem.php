<?php

namespace App\Cart;

use App\Entity\Gestapp\Product;

class CartItem
{
    public $product;
    public $qty;
    public $item;

    public function __construct(Product $product, int $qty, int $item)
    {
        $this->product = $product;
        $this->qty = $qty;
        $this->item = $item;
    }

    public function getTotal()
    {
        return $this->product->getPrice() * $this->qty;
    }
}