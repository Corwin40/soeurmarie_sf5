<?php

namespace App\Entity\Gestapp;

use App\Entity\Admin\Member;
use App\Repository\Gestapp\CartRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CartRepository::class)
 */
class Cart
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $productName;

    /**
     * @ORM\ManyToOne(targetEntity=ProductNature::class)
     */
    private $productNature;

    /**
     * @ORM\ManyToOne(targetEntity=ProductCategory::class)
     */
    private $productCategory;

    /**
     * @ORM\Column(type="integer")
     */
    private $productQty;

    /**
     * @ORM\Column(type="integer")
     */
    private $customIdformat;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $customFormat;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $customName;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     */
    private $customPrice;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     */
    private $customWeight;

    /**
     * @ORM\ManyToOne(targetEntity=Member::class)
     */
    private $member;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $uuid;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $productId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $productRef;

    /**
     * @ORM\Column(type="boolean")
     */
    private $productPerson = false;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="carts")
     */
    private $product;

    /**
     * @ORM\Column(type="integer")
     */
    private $item;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(?string $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    public function getProductNature(): ?ProductNature
    {
        return $this->productNature;
    }

    public function setProductNature(?ProductNature $productNature): self
    {
        $this->productNature = $productNature;

        return $this;
    }

    public function getProductCategory(): ?ProductCategory
    {
        return $this->productCategory;
    }

    public function setProductCategory(?ProductCategory $productCategory): self
    {
        $this->productCategory = $productCategory;

        return $this;
    }

    public function getProductQty(): ?int
    {
        return $this->productQty;
    }

    public function setProductQty(int $productQty): self
    {
        $this->productQty = $productQty;

        return $this;
    }

    public function getCustomIdformat(): ?int
    {
        return $this->customIdformat;
    }

    public function setCustomIdformat(int $customIdformat): self
    {
        $this->customIdformat = $customIdformat;

        return $this;
    }

    public function getCustomFormat(): ?string
    {
        return $this->customFormat;
    }

    public function setCustomFormat(string $customFormat): self
    {
        $this->customFormat = $customFormat;

        return $this;
    }

    public function getCustomName(): ?string
    {
        return $this->customName;
    }

    public function setCustomName(?string $customName): self
    {
        $this->customName = $customName;

        return $this;
    }

    public function getCustomPrice(): ?string
    {
        return $this->customPrice;
    }

    public function setCustomPrice(?string $customPrice): self
    {
        $this->customPrice = $customPrice;

        return $this;
    }

    public function getCustomWeight(): ?string
    {
        return $this->customWeight;
    }

    public function setCustomWeight(?string $customWeight): self
    {
        $this->customWeight = $customWeight;

        return $this;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function setProductId(?int $productId): self
    {
        $this->productId = $productId;

        return $this;
    }

    public function getProductRef(): ?string
    {
        return $this->productRef;
    }

    public function setProductRef(?string $productRef): self
    {
        $this->productRef = $productRef;

        return $this;
    }

    public function getProductPerson(): ?bool
    {
        return $this->productPerson;
    }

    public function setProductPerson(bool $productPerson): self
    {
        $this->productPerson = $productPerson;

        return $this;
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

    public function getItem(): ?int
    {
        return $this->item;
    }

    public function setItem(int $item): self
    {
        $this->item = $item;

        return $this;
    }
}
