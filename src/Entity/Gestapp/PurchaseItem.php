<?php

namespace App\Entity\Gestapp;

use App\Entity\Gestapp\Product;
use App\Repository\Gestapp\PurchaseItemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PurchaseItemRepository::class)
 */
class PurchaseItem
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="purchaseItems")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity=Purchase::class, inversedBy="purchaseItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $purchase;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $productName;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $productPrice;

    /**
     * @ORM\Column(type="float")
     */
    private $productQty;

    /**
     * @ORM\Column(type="float")
     */
    private $totalItem;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    private $CustomerName;

    /**
     * @ORM\ManyToOne(targetEntity=productFormat::class)
     */
    private $format;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getPurchase(): ?Purchase
    {
        return $this->purchase;
    }

    public function setPurchase(?Purchase $purchase): self
    {
        $this->purchase = $purchase;

        return $this;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    public function getProductPrice(): ?string
    {
        return $this->productPrice;
    }

    public function setProductPrice(string $productPrice): self
    {
        $this->productPrice = $productPrice;

        return $this;
    }

    public function getProductQty(): ?float
    {
        return $this->productQty;
    }

    public function setProductQty(float $productQty): self
    {
        $this->productQty = $productQty;

        return $this;
    }

    public function getTotalItem(): ?float
    {
        return $this->totalItem;
    }

    public function setTotalItem(float $totalItem): self
    {
        $this->totalItem = $totalItem;

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->CustomerName;
    }

    public function setCustomerName(?string $CustomerName): self
    {
        $this->CustomerName = $CustomerName;

        return $this;
    }

    public function getFormat(): ?productFormat
    {
        return $this->format;
    }

    public function setFormat(?productFormat $format): self
    {
        $this->format = $format;

        return $this;
    }
}
