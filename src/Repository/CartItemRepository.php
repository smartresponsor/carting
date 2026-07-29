<?php

declare(strict_types=1);

namespace App\Carting\Repository;

use App\Carting\Entity\CartItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<CartItem> */
final class CartItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartItem::class);
    }
}
