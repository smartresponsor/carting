<?php

declare(strict_types=1);

namespace App\Carting\Entity;

use App\Carting\Enum\CartAdjustmentType;
use App\Carting\Enum\CartStatus;
use App\Carting\Repository\CartRepository;
use App\Objecting\EntityInterface\ObjectAuditedInterface;
use App\Objecting\EntityTrait\Embeddable\ObjectAuditEmbeddableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartRepository::class)]
#[ORM\Table(name: 'cart_cart')]
#[ORM\Index(columns: ['cart_token'], name: 'cart_cart_token_idx')]
#[ORM\Index(columns: ['owner_reference'], name: 'cart_cart_owner_reference_idx')]
/**
 * Defines the Cart responsibility used by the Carting component runtime.
 */
class Cart implements ObjectAuditedInterface
{
    use ObjectAuditEmbeddableTrait;
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

    /** @var Collection<int, CartAdjustmentEntity> */
    #[ORM\OneToMany(mappedBy: 'cart', targetEntity: CartAdjustmentEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $adjustments;

    #[ORM\Column(name: 'expires_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Version]
    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $version = 1;

    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     */
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
        $this->adjustments = new ArrayCollection();
        $this->initializeObjectAudit();
    }

    /**
     * Returns the value produced by getId for this Carting runtime responsibility.
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    /**
     * Returns the value produced by getCartToken for this Carting runtime responsibility.
     */
    public function getCartToken(): string
    {
        return $this->cartToken;
    }
    /**
     * Returns the value produced by getOwnerReference for this Carting runtime responsibility.
     */
    public function getOwnerReference(): ?string
    {
        return $this->ownerReference;
    }
    /**
     * Returns the value produced by getCurrencyCode for this Carting runtime responsibility.
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }
    /**
     * Returns the value produced by getStatus for this Carting runtime responsibility.
     */
    public function getStatus(): CartStatus
    {
        return $this->status;
    }
    /**
     * Returns the value produced by getExpiresAt for this Carting runtime responsibility.
     */
    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }
    /**
     * Returns the value produced by getVersion for this Carting runtime responsibility.
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * Returns the value produced by getItems for this Carting runtime responsibility.
     * @return Collection<int, CartItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * Returns the value produced by getAdjustments for this Carting runtime responsibility.
     * @return Collection<int, CartAdjustmentEntity>
     */
    public function getAdjustments(): Collection
    {
        return $this->adjustments;
    }

    /**
     * Executes the assignOwner behavior owned by this Carting runtime responsibility.
     */
    public function assignOwner(string $ownerReference): void
    {
        $this->assertActiveFor('assign owner to');

        $ownerReference = trim($ownerReference);
        if ('' === $ownerReference) {
            throw new \InvalidArgumentException('Cart owner reference must not be empty.');
        }

        $this->ownerReference = $ownerReference;
        $this->touch();
    }

    /**
     * Executes the markCheckoutPending behavior owned by this Carting runtime responsibility.
     */
    public function markCheckoutPending(): void
    {
        $this->assertActiveFor('mark checkout pending for');
        $this->status = CartStatus::CheckoutPending;
        $this->touch();
    }

    /**
     * Executes the markConverted behavior owned by this Carting runtime responsibility.
     */
    public function markConverted(): void
    {
        if (CartStatus::CheckoutPending !== $this->status) {
            throw new \LogicException(sprintf(
                'Cannot convert cart "%s" because cart status is "%s".',
                $this->cartToken,
                $this->status->value,
            ));
        }

        $this->status = CartStatus::Converted;
        $this->touch();
    }

    /**
     * Executes the markMerged behavior owned by this Carting runtime responsibility.
     */
    public function markMerged(): void
    {
        $this->assertActiveFor('mark merged');
        $this->status = CartStatus::Merged;
        $this->touch();
    }

    /**
     * Executes the markExpired behavior owned by this Carting runtime responsibility.
     */
    public function markExpired(): void
    {
        $this->assertActiveFor('expire');
        $this->status = CartStatus::Expired;
        $this->touch();
    }

    /**
     * Executes the markAbandoned behavior owned by this Carting runtime responsibility.
     */
    public function markAbandoned(): void
    {
        $this->assertActiveFor('abandon');
        $this->status = CartStatus::Abandoned;
        $this->touch();
    }

    /**
     * Executes the setExpiresAt behavior owned by this Carting runtime responsibility.
     */
    public function setExpiresAt(?\DateTimeImmutable $expiresAt): void
    {
        $this->assertActiveFor('change expiration for');

        if (null !== $expiresAt && $expiresAt < $this->getCreatedAt()) {
            throw new \InvalidArgumentException('Cart expiration timestamp cannot precede creation timestamp.');
        }

        $this->expiresAt = $expiresAt;
        $this->touch();
    }

    /**
     * Executes the addItem behavior owned by this Carting runtime responsibility.
     */
    public function addItem(CartItem $item): void
    {
        $this->assertActiveFor('add item to');

        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->attachToCart($this);
            $this->touch();
        }
    }

    /**
     * Executes the removeItem behavior owned by this Carting runtime responsibility.
     */
    public function removeItem(CartItem $item): void
    {
        $this->assertActiveFor('remove item from');

        if ($this->items->removeElement($item)) {
            $this->touch();
        }
    }

    /**
     * Executes the addAdjustment behavior owned by this Carting runtime responsibility.
     */
    public function addAdjustment(CartAdjustmentEntity $adjustment): void
    {
        $this->assertActiveFor('add adjustment to');

        if ($adjustment->getCart() !== $this) {
            throw new \DomainException('Cart adjustment must belong to this cart.');
        }

        if (!$this->adjustments->contains($adjustment)) {
            $this->adjustments->add($adjustment);
            $this->touch();
        }
    }

    /**
     * Executes the removeAdjustmentsOfType behavior owned by this Carting runtime responsibility.
     */
    public function removeAdjustmentsOfType(CartAdjustmentType $type): void
    {
        $this->assertActiveFor('remove adjustments from');

        $changed = false;
        foreach ($this->adjustments->toArray() as $adjustment) {
            if ($adjustment->getType() === $type) {
                $this->adjustments->removeElement($adjustment);
                $changed = true;
            }
        }

        if ($changed) {
            $this->touch();
        }
    }

    /**
     * Updates the cart modification timestamp after a state-changing operation.
     */
    public function touch(): void
    {
        $this->touchModified();
    }

    /**
     * Executes the assertActiveFor behavior owned by this Carting runtime responsibility.
     */
    private function assertActiveFor(string $operation): void
    {
        if (CartStatus::Active === $this->status) {
            return;
        }

        throw new \LogicException(sprintf(
            'Cannot %s cart "%s" because cart status is "%s".',
            $operation,
            $this->cartToken,
            $this->status->value,
        ));
    }
}
