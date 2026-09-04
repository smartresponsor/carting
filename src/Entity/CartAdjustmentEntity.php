<?php

declare(strict_types=1);

namespace App\Carting\Entity;

use App\Carting\Enum\CartAdjustmentType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cart_adjustment')]
class CartAdjustmentEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'adjustments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Cart $cart;

    #[ORM\Column(type: 'string', length: 32, enumType: CartAdjustmentType::class)]
    private CartAdjustmentType $type;

    #[ORM\Column(name: 'label', type: 'string', length: 191)]
    private string $label;

    #[ORM\Column(name: 'amount_minor', type: 'integer')]
    private int $amountMinor;

    public function __construct(Cart $cart, CartAdjustmentType $type, string $label, int $amountMinor)
    {
        $label = trim($label);
        if ('' === $label) {
            throw new \InvalidArgumentException('Cart adjustment label must not be empty.');
        }

        $this->cart = $cart;
        $this->type = $type;
        $this->label = $label;
        $this->amountMinor = $amountMinor;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getCart(): Cart
    {
        return $this->cart;
    }
    public function getAmountMinor(): int
    {
        return $this->amountMinor;
    }
    public function getLabel(): string
    {
        return $this->label;
    }
    public function getType(): CartAdjustmentType
    {
        return $this->type;
    }
}
