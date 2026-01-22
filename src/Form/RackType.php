<?php

namespace App\Form;

use App\Entity\Display;
use App\Entity\Product;
use App\Entity\Rack;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('position')
            ->add('requiredQuantity')
            ->add('currentQuantity')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('display', EntityType::class, [
                'class' => Display::class,
                'choice_label' => 'id',
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rack::class,
        ]);
    }
}
