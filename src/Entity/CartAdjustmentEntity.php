<?php

declare(strict_types=1);

namespace App\Carting\Entity;

use App\Carting\Enum\CartAdjustmentType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cart_adjustment')]
#[ORM\Index(columns: ['cart_id'], name: 'idx_cart_adjustment_cart_id')]
/**
 * Defines the CartAdjustmentEntity responsibility used by the Carting component runtime.
 */
class CartAdjustmentEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CartEntity::class, inversedBy: 'adjustments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private CartEntity $cart;

    #[ORM\Column(type: 'string', length: 32, enumType: CartAdjustmentType::class)]
    private CartAdjustmentType $type;

    #[ORM\Column(name: 'label', type: 'string', length: 191)]
    private string $label;

    #[ORM\Column(name: 'amount_minor', type: 'integer')]
    private int $amountMinor;

    #[ORM\Column(name: 'source_reference', type: 'string', length: 191, nullable: true)]
    private ?string $sourceReference = null;

    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     */
    public function __construct(CartEntity $cart, CartAdjustmentType $type, string $label, int $amountMinor, ?string $sourceReference = null)
    {
        $label = trim($label);
        if ('' === $label) {
            throw new \InvalidArgumentException('Cart adjustment label must not be empty.');
        }

        $sourceReference = null === $sourceReference ? null : trim($sourceReference);
        if ('' === $sourceReference) {
            throw new \InvalidArgumentException('Cart adjustment source reference must not be empty when provided.');
        }

        $this->cart = $cart;
        $this->type = $type;
        $this->label = $label;
        $this->amountMinor = $amountMinor;
        $this->sourceReference = $sourceReference;
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
    public function getCart(): CartEntity
    {
        return $this->cart;
    }
    /**
     * Returns the value produced by getAmountMinor for this Carting runtime responsibility.
     */
    public function getAmountMinor(): int
    {
        return $this->amountMinor;
    }
    /**
     * Returns the value produced by getLabel for this Carting runtime responsibility.
     */
    public function getLabel(): string
    {
        return $this->label;
    }
    /**
     * Returns the value produced by getType for this Carting runtime responsibility.
     */
    public function getType(): CartAdjustmentType
    {
        return $this->type;
    }

    public function getSourceReference(): ?string
    {
        return $this->sourceReference;
    }
}
