<?php

declare(strict_types=1);

namespace App\Carting\Repository;

use App\Carting\Entity\CartEntity;
use App\Carting\Entity\CartCheckoutHandoffEntity;
use App\Carting\RepositoryInterface\CartCheckoutHandoffRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Persists checkout handoffs and their cart state in one Doctrine flush.
 *
 * @extends ServiceEntityRepository<CartCheckoutHandoffEntity>
 */
final class CartCheckoutHandoffRepository extends ServiceEntityRepository implements CartCheckoutHandoffRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartCheckoutHandoffEntity::class);
    }

    public function findForCart(CartEntity $cart): ?CartCheckoutHandoffEntity
    {
        return $this->findOneBy(['cart' => $cart]);
    }

    public function savePrepared(CartCheckoutHandoffEntity $handoff): void
    {
        $this->getEntityManager()->persist($handoff);
        $this->getEntityManager()->flush();
    }

    public function saveAccepted(CartCheckoutHandoffEntity $handoff): void
    {
        $this->getEntityManager()->persist($handoff);
        $this->getEntityManager()->persist($handoff->getCart());
        $this->getEntityManager()->flush();
    }
}
