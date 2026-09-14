<?php

declare(strict_types=1);

namespace App\Carting\Entity;

use App\Carting\Enum\CartStatus;
use App\Objecting\EntityInterface\ObjectAuditedInterface;
use App\Objecting\EntityTrait\Embeddable\ObjectAuditEmbeddableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cart_item')]
#[ORM\Index(columns: ['offer_reference'], name: 'cart_item_offer_reference_idx')]
/**
 * Defines the CartItem responsibility used by the Carting component runtime.
 */
class CartItem implements ObjectAuditedInterface
{
    use ObjectAuditEmbeddableTrait;
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

    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     * @param array<string, mixed> $metadata
     */
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
     * Returns the value produced by getCart for this Carting runtime responsibility.
     */
    public function getCart(): Cart
    {
        return $this->cart;
    }
    /**
     * Returns the value produced by getOfferReference for this Carting runtime responsibility.
     */
    public function getOfferReference(): string
    {
        return $this->offerReference;
    }
    /**
     * Returns the value produced by getTitleSnapshot for this Carting runtime responsibility.
     */
    public function getTitleSnapshot(): string
    {
        return $this->titleSnapshot;
    }
    /**
     * Returns the value produced by getUnitPriceMinor for this Carting runtime responsibility.
     */
    public function getUnitPriceMinor(): int
    {
        return $this->unitPriceMinor;
    }
    /**
     * Returns the value produced by getCurrencyCode for this Carting runtime responsibility.
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }
    /**
     * Returns the value produced by getQuantity for this Carting runtime responsibility.
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }
    /**
     * Returns the value produced by getMetadata for this Carting runtime responsibility.
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Executes the attachToCart behavior owned by this Carting runtime responsibility.
     */
    public function attachToCart(Cart $cart): void
    {
        if ($cart->getCurrencyCode() !== $this->currencyCode) {
            throw new \DomainException('Cart item currency must match cart currency.');
        }

        $this->cart = $cart;
    }

    /**
     * Executes the increaseBy behavior owned by this Carting runtime responsibility.
     */
    public function increaseBy(int $quantity): void
    {
        $this->assertOwningCartActive();

        if ($quantity < 1) {
            throw new \InvalidArgumentException('Increase quantity must be positive.');
        }
        $this->quantity += $quantity;
        $this->touch();
    }

    /**
     * Executes the changeQuantity behavior owned by this Carting runtime responsibility.
     */
    public function changeQuantity(int $quantity): void
    {
        $this->assertOwningCartActive();

        if ($quantity < 1) {
            throw new \InvalidArgumentException('Cart item quantity must be at least 1.');
        }
        $this->quantity = $quantity;
        $this->touch();
    }

    /**
     * Returns the value produced by getLineTotalMinor for this Carting runtime responsibility.
     */
    public function getLineTotalMinor(): int
    {
        return $this->unitPriceMinor * $this->quantity;
    }

    /**
     * Returns the value produced by touch for this Carting runtime responsibility.
     */
    private function touch(): void
    {
        $this->touchModified();
    }

    /**
     * Executes the assertOwningCartActive behavior owned by this Carting runtime responsibility.
     */
    private function assertOwningCartActive(): void
    {
        if (!isset($this->cart) || CartStatus::Active === $this->cart->getStatus()) {
            return;
        }

        throw new \LogicException(sprintf(
            'Cannot mutate cart item because owning cart status is "%s".',
            $this->cart->getStatus()->value,
        ));
    }
}
