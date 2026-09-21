<?php

declare(strict_types=1);

namespace App\Carting\Tests\Integration\Doctrine;

use App\Carting\Entity\Cart;
use App\Carting\Entity\CartAdjustmentEntity;
use App\Carting\Entity\CartCheckoutHandoffEntity;
use App\Carting\Entity\CartItem;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use PHPUnit\Framework\TestCase;

final class CartDoctrineMetadataTest extends TestCase
{
    public function testCartUsesDoctrineOptimisticVersioning(): void
    {
        $metadata = $this->loadMetadata(Cart::class);

        self::assertTrue($metadata->isVersioned);
        self::assertSame('version', $metadata->versionField);
        self::assertSame('integer', $metadata->getTypeOfField('version'));
        self::assertSame(1, $metadata->getFieldMapping('version')->options['default'] ?? null);
    }

    public function testCartTableContractIsExplicit(): void
    {
        $metadata = $this->loadMetadata(Cart::class);

        self::assertSame('cart_cart', $metadata->getTableName());
        self::assertSame(96, $metadata->getFieldMapping('cartToken')->length);
        self::assertSame(
            ['cart_token'],
            $metadata->table['uniqueConstraints']['uniq_cart_cart_token']['columns'] ?? null,
        );
        self::assertSame(191, $metadata->getFieldMapping('ownerReference')->length);
        self::assertTrue($metadata->getFieldMapping('ownerReference')->nullable ?? false);
        self::assertSame(3, $metadata->getFieldMapping('currencyCode')->length);
        self::assertSame(32, $metadata->getFieldMapping('status')->length);
        self::assertSame(['cart_token'], $metadata->table['indexes']['cart_cart_token_idx']['columns'] ?? null);
        self::assertSame(['owner_reference'], $metadata->table['indexes']['cart_cart_owner_reference_idx']['columns'] ?? null);
    }

    public function testCartItemTableContractIsExplicit(): void
    {
        $metadata = $this->loadMetadata(CartItem::class);

        self::assertSame('cart_item', $metadata->getTableName());
        self::assertSame('cart_id', $metadata->getSingleAssociationJoinColumnName('cart'));
        self::assertSame('CASCADE', $metadata->getAssociationMapping('cart')->joinColumns[0]->onDelete ?? null);
        self::assertSame(191, $metadata->getFieldMapping('offerReference')->length);
        self::assertSame(255, $metadata->getFieldMapping('titleSnapshot')->length);
        self::assertSame(3, $metadata->getFieldMapping('currencyCode')->length);
        self::assertSame('json', $metadata->getTypeOfField('metadata'));
        self::assertSame(['offer_reference'], $metadata->table['indexes']['cart_item_offer_reference_idx']['columns'] ?? null);
    }

    public function testCartAdjustmentTableContractIsExplicit(): void
    {
        $metadata = $this->loadMetadata(CartAdjustmentEntity::class);

        self::assertSame('cart_adjustment', $metadata->getTableName());
        self::assertSame('cart_id', $metadata->getSingleAssociationJoinColumnName('cart'));
        self::assertSame('CASCADE', $metadata->getAssociationMapping('cart')->joinColumns[0]->onDelete ?? null);
        self::assertSame('adjustments', $metadata->getAssociationMapping('cart')->inversedBy ?? null);
        self::assertSame(32, $metadata->getFieldMapping('type')->length);
        self::assertSame(191, $metadata->getFieldMapping('label')->length);
        self::assertSame(191, $metadata->getFieldMapping('sourceReference')->length);
        self::assertTrue($metadata->getFieldMapping('sourceReference')->nullable ?? false);
    }

    public function testCheckoutHandoffHasOnePerCartUniqueConstraint(): void
    {
        $metadata = $this->loadMetadata(CartCheckoutHandoffEntity::class);
        $constraints = $metadata->table['uniqueConstraints'] ?? [];

        self::assertArrayHasKey('cart_checkout_handoff_cart_unique', $constraints);
        self::assertSame(
            ['cart_id'],
            $constraints['cart_checkout_handoff_cart_unique']['columns'] ?? null,
        );
        self::assertSame('cart_id', $metadata->getSingleAssociationJoinColumnName('cart'));
        self::assertSame(191, $metadata->getFieldMapping('downstreamReference')->length);
        self::assertTrue($metadata->getFieldMapping('downstreamReference')->nullable ?? false);
        self::assertTrue($metadata->getFieldMapping('acceptedAt')->nullable ?? false);
    }

    /**
     * @param class-string<object> $entityClass
     * @return ClassMetadata<object>
     */
    private function loadMetadata(string $entityClass): ClassMetadata
    {
        $metadata = new ClassMetadata($entityClass);
        $metadata->initializeReflection(new RuntimeReflectionService());

        (new AttributeDriver([dirname(__DIR__, 4) . '/src/Entity']))
            ->loadMetadataForClass($entityClass, $metadata);

        return $metadata;
    }
}
