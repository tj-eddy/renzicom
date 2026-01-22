<?php

namespace App\Form;

use App\Entity\Warehouse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WarehouseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'warehouse.form.name.label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'warehouse.form.name.placeholder',
                ],
            ])
            ->add('address', TextareaType::class, [
                'label' => 'warehouse.form.address.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'warehouse.form.address.placeholder',
                    'rows' => 3,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Warehouse::class,
        ]);
    }
}
