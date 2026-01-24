<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'product.form.name.label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'product.form.name.placeholder',
                ],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'product.form.image.label',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'validation.product.image.invalid_type',
                        'maxSizeMessage' => 'validation.product.image.too_large',
                    ]),
                ],
            ])
            ->add('yearEdition', IntegerType::class, [
                'label' => 'product.form.year_edition.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'product.form.year_edition.placeholder',
                    'min' => 1900,
                    'max' => 2100,
                ],
            ])
            ->add('language', TextType::class, [
                'label' => 'product.form.language.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'product.form.language.placeholder',
                    'maxlength' => 10,
                ],
            ])
            ->add('variant', TextareaType::class, [
                'label' => 'product.form.variant.label',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'product.form.variant.placeholder',
                    'rows' => 4,
                ],
                'help' => 'product.form.variant.help',
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
