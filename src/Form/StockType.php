<?php

namespace App\Form;

use App\Entity\Stock;
use App\Entity\Rack;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire pour la gestion des stocks
 */
class StockType extends AbstractType
{
    /**
     * Construction du formulaire
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rack', EntityType::class, [
                'class' => Rack::class,
                'choice_label' => 'name',
                'label' => 'Rack',
                'placeholder' => 'Sélectionner un rack',
                'required' => true,
                'attr' => [
                    'class' => 'select2-rack',
                    'data-placeholder' => 'Rechercher un rack...',
                ],
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'label' => 'Produit',
                'placeholder' => 'Sélectionner un produit',
                'required' => true,
                'attr' => [
                    'class' => 'select2-product',
                    'data-placeholder' => 'Rechercher un produit...',
                ],
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Quantité à ajouter',
                'required' => true,
                'attr' => [
                    'min' => 1,
                ],
            ])
            ->add('note', TextareaType::class, [
                'label' => 'Note',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                ],
            ])
        ;
    }

    /**
     * Configuration des options du formulaire
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}
