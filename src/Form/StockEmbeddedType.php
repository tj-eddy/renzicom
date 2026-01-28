<?php

namespace App\Form;

use App\Entity\Stock;
use App\Entity\Warehouse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockEmbeddedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('warehouse', EntityType::class, [
                'class' => Warehouse::class,
                'choice_label' => 'name',
                'label' => 'stock.form.warehouse.label',
                'placeholder' => 'stock.form.warehouse.placeholder',
                'attr' => [
                    'class' => 'form-select warehouse-select'
                ],
                'required' => true
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'stock.form.quantity.label',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'placeholder' => '0'
                ],
                'empty_data' => 0,
                'required' => true
            ])
            ->add('note', TextareaType::class, [
                'label' => 'stock.form.note.label',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 2,
                    'placeholder' => 'stock.form.note.placeholder'
                ],
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}
