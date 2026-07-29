<?php

declare(strict_types=1);

namespace App\Carting\Entity;

use App\Carting\Repository\CartItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartItemRepository::class)]
#[ORM\Table(name: 'cart_item')]
#[ORM\Index(columns: ['offer_reference'], name: 'cart_item_offer_reference_idx')]
class CartItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Cart $cart;

    #[ORM\Column(name: 'offer_reference', type: 'string', length: 191)]
    private string $offerReference;

    #[ORM\Column(name: 'title_snapshot', type: 'string', length: 255)]
    private string $titleSnapshot;

    #[ORM\Column(name: 'unit_price_minor', type: 'integer')]
    private int $unitPriceMinor;

    #[ORM\Column(name: 'currency_code', type: 'string', length: 3)]
    private string $currencyCode;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    /** @var array<string, mixed> */
    #[ORM\Column(name: 'metadata', type: 'json')]
    private array $metadata = [];

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    /** @param array<string, mixed> $metadata */
    public function __construct(string $offerReference, string $titleSnapshot, int $unitPriceMinor, string $currencyCode, int $quantity, array $metadata = [])
    {
        $offerReference = trim($offerReference);
        $titleSnapshot = trim($titleSnapshot);
        $currencyCode = strtoupper(trim($currencyCode));

        if ('' === $offerReference) {
            throw new \InvalidArgumentException('Cart item offer reference must not be empty.');
        }

        if ('' === $titleSnapshot) {
            throw new \InvalidArgumentException('Cart item title snapshot must not be empty.');
        }

        if ($unitPriceMinor < 0) {
            throw new \InvalidArgumentException('Cart item unit price must not be negative.');
        }

        if (1 !== preg_match('/^[A-Z]{3}$/', $currencyCode)) {
            throw new \InvalidArgumentException('Cart item currency code must be a three-letter ISO-style code.');
        }

        if ($quantity < 1) {
            throw new \InvalidArgumentException('Cart item quantity must be at least 1.');
        }

        $this->offerReference = $offerReference;
        $this->titleSnapshot = $titleSnapshot;
        $this->unitPriceMinor = $unitPriceMinor;
        $this->currencyCode = $currencyCode;
        $this->quantity = $quantity;
        $this->metadata = $metadata;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getCart(): Cart
    {
        return $this->cart;
    }
    public function getOfferReference(): string
    {
        return $this->offerReference;
    }
    public function getTitleSnapshot(): string
    {
        return $this->titleSnapshot;
    }
    public function getUnitPriceMinor(): int
    {
        return $this->unitPriceMinor;
    }
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }
    public function getQuantity(): int
    {
        return $this->quantity;
    }
    /** @return array<string, mixed> */
    public function getMetadata(): array
    {
        return $this->metadata;
    }
    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function attachToCart(Cart $cart): void
    {
        $this->cart = $cart;
    }

    public function increaseBy(int $quantity): void
    {
        if ($quantity < 1) {
            throw new \InvalidArgumentException('Increase quantity must be positive.');
        }
        $this->quantity += $quantity;
        $this->touch();
    }

    public function changeQuantity(int $quantity): void
    {
        if ($quantity < 1) {
            throw new \InvalidArgumentException('Cart item quantity must be at least 1.');
        }
        $this->quantity = $quantity;
        $this->touch();
    }

    public function getLineTotalMinor(): int
    {
        return $this->unitPriceMinor * $this->quantity;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
