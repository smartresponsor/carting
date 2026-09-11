<?php

declare(strict_types=1);

namespace App\Carting\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Defines the CartItemQuantityType responsibility used by the Carting component runtime.
 */
final class CartItemQuantityType extends AbstractType
{
    /**
     * Executes the buildForm behavior owned by this Carting runtime responsibility.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('quantity', IntegerType::class);
    }
}
