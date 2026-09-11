<?php

declare(strict_types=1);

namespace App\Carting\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the CartAddItemType responsibility used by the Carting component runtime.
 */
final class CartAddItemType extends AbstractType
{
    /**
     * Executes the buildForm behavior owned by this Carting runtime responsibility.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('offerReference', HiddenType::class)
            ->add('quantity', IntegerType::class);
    }

    /**
     * Executes the configureOptions behavior owned by this Carting runtime responsibility.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['csrf_protection' => true]);
    }
}
