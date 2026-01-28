<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Rack;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RackEmbeddedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'rack.form.name.label',
                'attr' => [
                    'class' => 'form-control form-control-sm',
                    'placeholder' => 'rack.form.name.placeholder'
                ],
                'required' => true
            ])
            ->add('position', IntegerType::class, [
                'label' => 'rack.form.position.label',
                'attr' => [
                    'class' => 'form-control form-control-sm',
                    'min' => 0
                ],
                'required' => true,
                'empty_data' => '0'
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'label' => 'rack.form.product.label',
                'placeholder' => 'rack.form.product.placeholder',
                'attr' => [
                    'class' => 'form-select form-select-sm product-select'
                ],
                'required' => false
            ])
            ->add('requiredQuantity', IntegerType::class, [
                'label' => 'rack.form.required_quantity.label',
                'attr' => [
                    'class' => 'form-control form-control-sm required-qty',
                    'min' => 0,
                    'placeholder' => '0'
                ],
                'required' => true,
                'empty_data' => '0'
            ])
            ->add('currentQuantity', IntegerType::class, [
                'label' => 'rack.form.current_quantity.label',
                'attr' => [
                    'class' => 'form-control form-control-sm current-qty',
                    'min' => 0,
                    'placeholder' => '0'
                ],
                'required' => false,
                'empty_data' => '0'
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
