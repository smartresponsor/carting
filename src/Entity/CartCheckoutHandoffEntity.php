<?php

declare(strict_types=1);

namespace App\Carting\Entity;

use App\Objecting\EntityInterface\ObjectAuditedInterface;
use App\Objecting\EntityTrait\Embeddable\ObjectAuditEmbeddableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cart_checkout_handoff')]
#[ORM\UniqueConstraint(name: 'cart_checkout_handoff_cart_unique', columns: ['cart_id'])]
#[ORM\Index(columns: ['handoff_reference'], name: 'cart_checkout_handoff_reference_idx')]
/**
 * Defines the CartCheckoutHandoffEntity responsibility used by the Carting component runtime.
 */
class CartCheckoutHandoffEntity implements ObjectAuditedInterface
{
    use ObjectAuditEmbeddableTrait;
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

    #[ORM\Column(name: 'downstream_reference', type: 'string', length: 191, nullable: true)]
    private ?string $downstreamReference = null;

    #[ORM\Column(name: 'accepted_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $acceptedAt = null;

    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     * @param array<string, mixed> $payload
     */
    public function __construct(Cart $cart, string $handoffReference, array $payload)
    {
        $handoffReference = trim($handoffReference);
        if ('' === $handoffReference) {
            throw new \InvalidArgumentException('Cart checkout handoff reference must not be empty.');
        }

        $this->cart = $cart;
        $this->handoffReference = $handoffReference;
        $this->payload = $payload;
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
     * Returns the value produced by getHandoffReference for this Carting runtime responsibility.
     */
    public function getHandoffReference(): string
    {
        return $this->handoffReference;
    }
    /**
     * Returns the value produced by getPayload for this Carting runtime responsibility.
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
    /**
     * Returns the value produced by getDownstreamReference for this Carting runtime responsibility.
     */
    public function getDownstreamReference(): ?string
    {
        return $this->downstreamReference;
    }
    /**
     * Returns the value produced by getAcceptedAt for this Carting runtime responsibility.
     */
    public function getAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    /**
     * Executes the markAccepted behavior owned by this Carting runtime responsibility.
     */
    public function markAccepted(string $downstreamReference): void
    {
        $downstreamReference = trim($downstreamReference);
        if ('' === $downstreamReference) {
            throw new \InvalidArgumentException('Cart checkout downstream reference must not be empty.');
        }

        if (null !== $this->downstreamReference && $this->downstreamReference !== $downstreamReference) {
            throw new \LogicException('Cart checkout handoff is already accepted with a different downstream reference.');
        }

        if (null === $this->downstreamReference) {
            $acceptedAt = new \DateTimeImmutable();
            $this->downstreamReference = $downstreamReference;
            $this->acceptedAt = $acceptedAt;
            $this->touchModified($acceptedAt);
        }
    }
}
