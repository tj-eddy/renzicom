<?php

namespace App\Form;

use App\Entity\Display;
use App\Entity\Product;
use App\Entity\Rack;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('display', EntityType::class, [
                'class' => Display::class,
                'choice_label' => function (Display $display) {
                    return $display->getName().' ('.$display->getHotel()->getName().')';
                },
                'label' => 'rack.form.display.label',
                'required' => true,
                'placeholder' => 'rack.form.display.placeholder',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'rack.form.name.label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'rack.form.name.placeholder',
                ],
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'label' => 'rack.form.product.label',
                'required' => false,
                'placeholder' => 'rack.form.product.placeholder',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('position', IntegerType::class, [
                'label' => 'rack.form.position.label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'rack.form.position.placeholder',
                    'min' => 0,
                ],
            ])
            ->add('requiredQuantity', IntegerType::class, [
                'label' => 'rack.form.required_quantity.label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'rack.form.required_quantity.placeholder',
                    'min' => 0,
                ],
            ])
            ->add('currentQuantity', IntegerType::class, [
                'label' => 'rack.form.current_quantity.label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'rack.form.current_quantity.placeholder',
                    'min' => 0,
                ],
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
