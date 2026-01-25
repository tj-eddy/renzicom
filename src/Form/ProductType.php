<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Stock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => [
                    'placeholder' => 'Ex: Watchtower 2025',
                    'class' => 'form-control'
                ],
                'required' => true
            ])
            ->add('yearEdition', IntegerType::class, [
                'label' => 'Année d\'édition',
                'attr' => [
                    'placeholder' => date('Y'),
                    'class' => 'form-control',
                    'min' => 2000,
                    'max' => 2100
                ],
                'required' => false
            ])
            ->add('language', ChoiceType::class, [
                'label' => 'Langue',
                'choices' => [
                    'Français' => 'fr',
                    'English' => 'en',
                    'Malagasy' => 'mg',
                    'Español' => 'es',
                    'Deutsch' => 'de',
                    'Italiano' => 'it',
                    'Português' => 'pt',
                ],
                'attr' => ['class' => 'form-select'],
                'placeholder' => 'Sélectionner une langue',
                'required' => false
            ])
            ->add('image', FileType::class, [
                'label' => 'Image du produit',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'constraints' => [
                    new File(maxSize: '2M', mimeTypes: [
                        'image/jpeg',
                        'image/jpg',
                        'image/png',
                    ], mimeTypesMessage: 'Veuillez télécharger une image valide (JPG, PNG)')
                ],
            ])
            ->add('stocks', CollectionType::class, [
                'entry_type' => StockEmbeddedType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'entry_options' => [
                    'label' => false,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
