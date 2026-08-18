<?php

declare(strict_types=1);

namespace App\Carting\Entity;

use App\Carting\Enum\CartStatus;
use App\Carting\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartRepository::class)]
#[ORM\Table(name: 'cart_cart')]
#[ORM\Index(columns: ['cart_token'], name: 'cart_cart_token_idx')]
#[ORM\Index(columns: ['owner_reference'], name: 'cart_cart_owner_reference_idx')]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'cart_token', type: 'string', length: 96, unique: true)]
    private string $cartToken;

    #[ORM\Column(name: 'owner_reference', type: 'string', length: 191, nullable: true)]
    private ?string $ownerReference = null;

    #[ORM\Column(name: 'currency_code', type: 'string', length: 3)]
    private string $currencyCode;

    #[ORM\Column(type: 'string', length: 32, enumType: CartStatus::class)]
    private CartStatus $status = CartStatus::Active;

    /** @var Collection<int, CartItem> */
    #[ORM\OneToMany(mappedBy: 'cart', targetEntity: CartItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(name: 'expires_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Version]
    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $version = 1;

    public function __construct(string $cartToken, string $currencyCode, ?string $ownerReference = null)
    {
        $cartToken = trim($cartToken);
        $currencyCode = strtoupper(trim($currencyCode));

        if ('' === $cartToken) {
            throw new \InvalidArgumentException('Cart token must not be empty.');
        }

        if (1 !== preg_match('/^[A-Z]{3}$/', $currencyCode)) {
            throw new \InvalidArgumentException('Cart currency code must be a three-letter ISO-style code.');
        }

        $ownerReference = null === $ownerReference ? null : trim($ownerReference);
        if ('' === $ownerReference) {
            throw new \InvalidArgumentException('Cart owner reference must not be empty when provided.');
        }

        $this->cartToken = $cartToken;
        $this->currencyCode = $currencyCode;
        $this->ownerReference = $ownerReference;
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getCartToken(): string
    {
        return $this->cartToken;
    }
    public function getOwnerReference(): ?string
    {
        return $this->ownerReference;
    }
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }
    public function getStatus(): CartStatus
    {
        return $this->status;
    }
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }
    public function getVersion(): int
    {
        return $this->version;
    }

    /** @return Collection<int, CartItem> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function assignOwner(string $ownerReference): void
    {
        $ownerReference = trim($ownerReference);
        if ('' === $ownerReference) {
            throw new \InvalidArgumentException('Cart owner reference must not be empty.');
        }

        $this->ownerReference = $ownerReference;
        $this->touch();
    }

    public function markConverted(): void
    {
        $this->status = CartStatus::Converted;
        $this->touch();
    }

    public function markExpired(): void
    {
        $this->status = CartStatus::Expired;
        $this->touch();
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): void
    {
        if (null !== $expiresAt && $expiresAt < $this->createdAt) {
            throw new \InvalidArgumentException('Cart expiration timestamp cannot precede creation timestamp.');
        }

        $this->expiresAt = $expiresAt;
        $this->touch();
    }

    public function addItem(CartItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->attachToCart($this);
            $this->touch();
        }
    }

    public function removeItem(CartItem $item): void
    {
        if ($this->items->removeElement($item)) {
            $this->touch();
        }
    }

    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
