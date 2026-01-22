<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Stock;
use App\Entity\Warehouse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('warehouse', EntityType::class, [
                'class' => Warehouse::class,
                'choice_label' => 'name',
                'label' => 'stock.form.warehouse.label',
                'required' => true,
                'placeholder' => 'stock.form.warehouse.placeholder',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'label' => 'stock.form.product.label',
                'required' => true,
                'placeholder' => 'stock.form.product.placeholder',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'stock.form.quantity.label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'stock.form.quantity.placeholder',
                    'min' => 0,
                ],
            ])
            ->add('note', TextareaType::class, [
                'label' => 'stock.form.note.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'stock.form.note.placeholder',
                    'rows' => 4,
                ],
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
