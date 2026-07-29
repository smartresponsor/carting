<?php

declare(strict_types=1);

namespace App\Carting\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cart_checkout_handoff')]
#[ORM\UniqueConstraint(name: 'cart_checkout_handoff_cart_unique', columns: ['cart_id'])]
#[ORM\Index(columns: ['handoff_reference'], name: 'cart_checkout_handoff_reference_idx')]
class CartCheckoutHandoffEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cart::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Cart $cart;

    #[ORM\Column(name: 'handoff_reference', type: 'string', length: 96, unique: true)]
    private string $handoffReference;

    /** @var array<string, mixed> */
    #[ORM\Column(name: 'payload', type: 'json')]
    private array $payload;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    /** @param array<string, mixed> $payload */
    public function __construct(Cart $cart, string $handoffReference, array $payload)
    {
        $this->cart = $cart;
        $this->handoffReference = $handoffReference;
        $this->payload = $payload;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getCart(): Cart
    {
        return $this->cart;
    }
    public function getHandoffReference(): string
    {
        return $this->handoffReference;
    }
    /** @return array<string, mixed> */
    public function getPayload(): array
    {
        return $this->payload;
    }
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
