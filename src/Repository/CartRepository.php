<?php

declare(strict_types=1);

namespace App\Carting\Repository;

use App\Carting\Entity\Cart;
use App\Carting\Enum\CartStatus;
use App\Carting\RepositoryInterface\CartRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Cart> */
final class CartRepository extends ServiceEntityRepository implements CartRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    public function findActiveByToken(string $cartToken): ?Cart
    {
        return $this->findOneBy(['cartToken' => $cartToken, 'status' => CartStatus::Active]);
    }

    public function findActiveByOwnerReference(string $ownerReference): ?Cart
    {
        return $this->findOneBy(['ownerReference' => $ownerReference, 'status' => CartStatus::Active]);
    }

    public function save(Cart $cart): void
    {
        $this->getEntityManager()->persist($cart);
        $this->getEntityManager()->flush();
    }
}
