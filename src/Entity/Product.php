<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\Product\ImageHandler;
use Doctrine\ORM\Mapping as ORM;
use Money\Money;
use App\Converter\MoneyConverter;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @Vich\Uploadable()
 */
class Product
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_CANCELLED = 2;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     *
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank()
     */
    private $shortDescription;

    /**
     * User
     *
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="products")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $imageFilename;

    /**
     * @Vich\UploadableField(mapping="products_images", fileNameProperty="imageFilename")
     */
    private $imageFile;

    /**
     * @param boolean
     */
    private $isRemoveImage;

    /**
     * @ORM\Embedded(class="\Money\Money")
     */
    private $price;

    /**
     * Fake price for forms
     *
     * @param int
     */
    private $fakePrice;

    /**
     * @ORM\Column(type="string", length=16)
     *
     * @Assert\NotBlank()
     */
    private $phone;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status = self::STATUS_PENDING;

    /**
     * Category
     *
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="products")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $category;

    /**
     * Get object unique id
     *
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string|null $name
     *
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set short description
     *
     * @param string|null $shortDescription
     *
     * @return self
     */
    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;
        return $this;
    }

    /**
     * Get short description
     *
     * @return string
     */
    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    /**
     * Set user
     *
     * @param User|null $user
     *
     * @return self
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set image file name
     *
     * @param string|null $imageFilename
     *
     * @return self
     */
    public function setImageFilename(?string $imageFilename): self
    {
        $this->imageFilename = $imageFilename;
        return $this;
    }

    /**
     * Get image file name
     *
     * @return string|null
     */
    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    /**
     * Set price
     *
     * @param int $price
     * @param string $currency
     *
     * @return self
     */
    public function setPrice(int $price, string $currency): self
    {
        $this->price = MoneyConverter::createMoneyObject($price, $currency);
        return $this;
    }

    /**
     * Get price
     *
     * @return Money|null
     */
    public function getPrice(): ?Money
    {
        return $this->price;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency(): string
    {
        $price = $this->getPrice();

        return $price ? $price->getCurrency() : MoneyConverter::MAIN_CURRENCY_ISO;
    }

    /**
     * Set fake price
     *
     * @param int|null $fakePrice
     *
     * @return self
     */
    public function setFakePrice(?int $fakePrice): self
    {
        $fakePrice = is_null($fakePrice) ? 0 : $fakePrice;
        $this->setPrice($fakePrice, $this->getCurrency());

        return $this;
    }

    /**
     * Get fake price
     *
     * @return int
     */
    public function getFakePrice(): int
    {
        $price = $this->getPrice();

        return $price ? $price->getAmount() : 0;
    }

    /**
     * Get display price
     *
     * @return string
     */
    public function getDisplayPrice(): string
    {
        $price = $this->getPrice();

        return $price ? MoneyConverter::getFormattedMoney($price) : '';
    }

    /**
     * Delete image product
     *
     * @param ImageHandler $imageHandler
     *
     * @return void
     */
    public function deleteImageProduct(ImageHandler $imageHandler)
    {
        if ($imageFilename = $this->getImageFilename()) {
            $this->setImageFilename(null);
            $imageHandler->remove($imageFilename);
        }
    }

    /**
     * Upload image product
     *
     * @param UploadedFile $imageFile
     * @param ImageHandler $imageHandler
     *
     * @return void
     */
    public function uploadImageProduct(UploadedFile $imageFile, ImageHandler $imageHandler)
    {
        $imageFilename = $imageHandler->upload($imageFile);
        $this->setImageFilename($imageFilename);
    }

    /**
     * Set isRemoveImage
     *
     * @param boolean $isRemoveImage
     *
     * @return self
     */
    public function setIsRemoveImage(bool $isRemoveImage): self
    {
        $this->isRemoveImage = $isRemoveImage;
        return $this;
    }

    /**
     * Get isRemoveImage
     *
     * @return boolean
     */
    public function getIsRemoveImage(): bool
    {
        return $this->isRemoveImage;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * Set phone
     *
     * @param string|null $phone
     *
     * @return self
     */
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Return image file
     *
     * @return mixed
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * Set image file
     *
     * @param mixed $imageFile
     *
     * @return self
     */
    public function setImageFile($imageFile): self
    {
        $this->imageFile = $imageFile;

        return $this;
    }

    /**
     * Return list all statuses for product
     *
     * @return array
     */
    public static function getAllStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_CANCELLED => 'Cancelled'
        ];
    }

    /**
     * Return status text
     *
     * @return string
     */
    public function getStatusText(): string
    {
        $allStatuses = self::getAllStatuses();

        return $allStatuses[$this->getStatus()];
    }

    /**
     * Return status
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param int $status
     *
     * @return self
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Set category
     *
     * @param Category|null $category
     *
     * @return self
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Get category
     *
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }
}