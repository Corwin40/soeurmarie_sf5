<?php

namespace App\Entity\Gestapp;

use App\Entity\Admin\Member;
use App\Repository\Gestapp\ProductRepository;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable()
 * @ORM\Table(name="product", indexes={@ORM\Index(columns={"name","description","ref"}, flags={"fulltext"})})
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $ref;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $details;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(type="float")
     */
    private $tva;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity=Member::class, inversedBy="products")
     */
    private $producer;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDisponible;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isOnLine = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isStar = false;

    /**
     * @ORM\ManyToOne(targetEntity=ProductNature::class, inversedBy="product")
     */
    private $productNature;

    /**
     * @ORM\ManyToOne(targetEntity=ProductUnit::class, inversedBy="product")
     */
    private $productUnit;

    /**
     * @ORM\ManyToOne(targetEntity=ProductCategory::class)
     */
    private $ProductCategory;

    /**
     * @ORM\OneToMany(targetEntity=PurchaseItem::class, mappedBy="product")
     */
    private $purchaseItems;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPersonalisable = false;

    /**
     * Insertion de l'image mise en avant liée à un article
     * NOTE : Il ne s'agit pas d'un champ mappé des métadonnées de l'entité, mais d'une simple propriété.
     *
     * @Vich\UploadableField(mapping="product_image_card", fileNameProperty="productName", size="productSize")
     * @var File|null
     */
    private $productFile;

    /**
     * @ORM\Column(type="string",nullable=true)
     *
     * @var string|null
     */
    private $productName;

    /**
     * @ORM\Column(type="integer",nullable=true)
     *
     * @var int|null
     */
    private $productSize;

    /**
     * Insertion de l'image mise en avant liée à un article
     * NOTE : Il ne s'agit pas d'un champ mappé des métadonnées de l'entité, mais d'une simple propriété.
     *
     * @Vich\UploadableField(mapping="verso_image_card", fileNameProperty="versoName", size="versoSize")
     * @var File|null
     */
    private $versoFile;

    /**
     * @ORM\Column(type="string",nullable=true)
     *
     * @var string|null
     */
    private $versoName;

    /**
     * @ORM\Column(type="integer",nullable=true)
     *
     * @var int|null
     */
    private $versoSize;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=ProductCustomize::class, mappedBy="product", orphanRemoval=true)
     */
    private $productCustomizes;

    /**
     * @ORM\ManyToMany(targetEntity=productFormat::class)
     */
    private $formats;

    /**
     * @ORM\ManyToMany(targetEntity=ProductCategory::class)
     *
     */
    private $otherCategory;

    /**
     * @ORM\OneToMany(targetEntity=Cart::class, mappedBy="product")
     */
    private $carts;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    public function __construct()
    {
        $this->purchaseItems = new ArrayCollection();
        $this->productCustomizes = new ArrayCollection();
        $this->otherCategory = new ArrayCollection();
        $this->formats = new ArrayCollection();
        $this->carts = new ArrayCollection();
    }

    /**
     * Permet d'initialiser le slug !
     * Utilisation de slugify pour transformer une chaine de caractères en slug
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function initializeSlug() {
        $slugify = new Slugify();
        $this->slug = $slugify->slugify($this->name);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Si vous téléchargez manuellement un fichier (c'est-à-dire sans utiliser Symfony Form),
     * assurez-vous qu'une instance de "UploadedFile" est injectée dans ce paramètre pour déclencher la mise à jour.
     * Si le paramètre de configuration 'inject_on_load' de ce bundle est défini sur 'true', ce setter doit être
     * capable d'accepter une instance de 'File' car le bundle en injectera une ici pendant l'hydratation de Doctrine.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $productFile
     */
    public function setProductFile(?File $productFile = null): void
    {
        $this->productFile = $productFile;

        if (null !== $productFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getProductFile(): ?File
    {
        return $this->productFile;
    }

    public function setProductName(?string $productName): void
    {
        $this->productName = $productName;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductSize(?int $productSize): void
    {
        $this->productSize = $productSize;
    }

    public function getProductSize(): ?int
    {
        return $this->productSize;
    }


    /**
     * Si vous téléchargez manuellement un fichier (c'est-à-dire sans utiliser Symfony Form),
     * assurez-vous qu'une instance de "UploadedFile" est injectée dans ce paramètre pour déclencher la mise à jour.
     * Si le paramètre de configuration 'inject_on_load' de ce bundle est défini sur 'true', ce setter doit être
     * capable d'accepter une instance de 'File' car le bundle en injectera une ici pendant l'hydratation de Doctrine.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $versoFile
     */
    public function setVersoFile(?File $versoFile = null): void
    {
        $this->versoFile = $versoFile;

        if (null !== $versoFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getVersoFile(): ?File
    {
        return $this->versoFile;
    }

    public function setVersoName(?string $versoName): void
    {
        $this->versoName = $versoName;
    }

    public function getVersoName(): ?string
    {
        return $this->versoName;
    }

    public function setVersoSize(?int $versoSize): void
    {
        $this->versoSize = $versoSize;
    }

    public function getVersoSize(): ?int
    {
        return $this->versoSize;
    }


    public function getCategory(): ?ProductCategory
    {
        return $this->category;
    }

    public function setCategory(?ProductCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist()
     */
    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime('now');
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function setUpdatedAt(): self
    {
        $this->updatedAt = new \DateTime('now');
        return $this;
    }


    public function getProducer(): ?Member
    {
        return $this->producer;
    }

    public function setProducer(?Member $producer): self
    {
        $this->producer = $producer;

        return $this;
    }

    public function getIsDisponible(): ?bool
    {
        return $this->isDisponible;
    }

    public function setIsDisponible(bool $isDisponible): self
    {
        $this->isDisponible = $isDisponible;

        return $this;
    }

    public function getIsOnLine(): ?bool
    {
        return $this->isOnLine;
    }

    public function setIsOnLine(bool $isOnLine): self
    {
        $this->isOnLine = $isOnLine;

        return $this;
    }

    public function getIsStar(): ?bool
    {
        return $this->isStar;
    }

    public function setIsStar(bool $isStar): self
    {
        $this->isStar = $isStar;

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

    public function getProductUnit(): ?ProductUnit
    {
        return $this->productUnit;
    }

    public function setProductUnit(?ProductUnit $productUnit): self
    {
        $this->productUnit = $productUnit;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = strtoupper($ref);

        return $this;
    }

    public function getProductCategory(): ?ProductCategory
    {
        return $this->ProductCategory;
    }

    public function setProductCategory(?ProductCategory $ProductCategory): self
    {
        $this->ProductCategory = $ProductCategory;

        return $this;
    }

    /**
     * @return Collection|PurchaseItem[]
     */
    public function getPurchaseItems(): Collection
    {
        return $this->purchaseItems;
    }

    public function addPurchaseItem(PurchaseItem $purchaseItem): self
    {
        if (!$this->purchaseItems->contains($purchaseItem)) {
            $this->purchaseItems[] = $purchaseItem;
            $purchaseItem->setProduct($this);
        }

        return $this;
    }

    public function removePurchaseItem(PurchaseItem $purchaseItem): self
    {
        if ($this->purchaseItems->removeElement($purchaseItem)) {
            // set the owning side to null (unless already changed)
            if ($purchaseItem->getProduct() === $this) {
                $purchaseItem->setProduct(null);
            }
        }

        return $this;
    }

    public function getTva(): ?float
    {
        return $this->tva;
    }

    public function setTva(float $tva): self
    {
        $this->tva = $tva;

        return $this;
    }

    public function getIsPersonalisable(): ?bool
    {
        return $this->isPersonalisable;
    }

    public function setIsPersonalisable(bool $isPersonalisable): self
    {
        $this->isPersonalisable = $isPersonalisable;

        return $this;
    }

    /**
     * @return Collection|ProductCustomize[]
     */
    public function getProductCustomizes(): Collection
    {
        return $this->productCustomizes;
    }

    public function addProductCustomize(ProductCustomize $productCustomize): self
    {
        if (!$this->productCustomizes->contains($productCustomize)) {
            $this->productCustomizes[] = $productCustomize;
            $productCustomize->setProduct($this);
        }

        return $this;
    }

    public function removeProductCustomize(ProductCustomize $productCustomize): self
    {
        if ($this->productCustomizes->removeElement($productCustomize)) {
            // set the owning side to null (unless already changed)
            if ($productCustomize->getProduct() === $this) {
                $productCustomize->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, productFormat>
     */
    public function getFormats(): Collection
    {
        return $this->formats;
    }

    public function addFormats(productFormat $formats): self
    {
        if (!$this->formats->contains($formats)) {
            $this->formats[] = $formats;
        }

        return $this;
    }

    public function removeFormats(productFormat $formats): self
    {
        $this->formats->removeElement($formats);

        return $this;
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    public function getOtherCategory(): Collection
    {
        return $this->otherCategory;
    }

    public function addOtherCategory(ProductCategory $otherCategory): self
    {
        if (!$this->otherCategory->contains($otherCategory)) {
            $this->otherCategory[] = $otherCategory;
        }

        return $this;
    }

    public function removeOtherCategory(ProductCategory $otherCategory): self
    {
        $this->otherCategory->removeElement($otherCategory);

        return $this;
    }

    /**
     * @return Collection<int, Cart>
     */
    public function getCarts(): Collection
    {
        return $this->carts;
    }

    public function addCart(Cart $cart): self
    {
        if (!$this->carts->contains($cart)) {
            $this->carts[] = $cart;
            $cart->setProduct($this);
        }

        return $this;
    }

    public function removeCart(Cart $cart): self
    {
        if ($this->carts->removeElement($cart)) {
            // set the owning side to null (unless already changed)
            if ($cart->getProduct() === $this) {
                $cart->setProduct(null);
            }
        }

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
