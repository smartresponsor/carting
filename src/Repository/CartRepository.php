<?php

declare(strict_types=1);

namespace App\Carting\Repository;

use App\Carting\Entity\CartEntity;
use App\Carting\Enum\CartStatus;
use App\Carting\RepositoryInterface\CartRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Defines the CartRepository responsibility used by the Carting component runtime.
 * @extends ServiceEntityRepository<CartEntity>
 */
final class CartRepository extends ServiceEntityRepository implements CartRepositoryInterface
{
    /**
     * Initializes the dependencies and state required by this Carting runtime responsibility.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartEntity::class);
    }

    /**
     * Returns the value produced by findActiveByToken for this Carting runtime responsibility.
     */
    public function findActiveByToken(string $cartToken): ?CartEntity
    {
        return $this->findOneBy(['cartToken' => $cartToken, 'status' => CartStatus::Active]);
    }

    /**
     * Returns the value produced by findActiveByOwnerReference for this Carting runtime responsibility.
     */
    public function findActiveByOwnerReference(string $ownerReference): ?CartEntity
    {
        return $this->findOneBy(['ownerReference' => $ownerReference, 'status' => CartStatus::Active]);
    }

    /**
     * Executes the save behavior owned by this Carting runtime responsibility.
     */
    public function save(CartEntity $cart): void
    {
        $this->getEntityManager()->persist($cart);
        $this->getEntityManager()->flush();
    }
}
