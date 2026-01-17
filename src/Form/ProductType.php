<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

/**
 * Product form type
 */
class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentYear = (int) date('Y');

        $builder
            ->add('name', TextType::class, [
                'label' => 'product.form.name',
                'attr' => [
                    'placeholder' => 'product.form.name.placeholder',
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('yearEdition', IntegerType::class, [
                'label' => 'product.form.year_edition',
                'attr' => [
                    'placeholder' => 'product.form.year_edition.placeholder',
                    'class' => 'form-control',
                    'min' => 2000,
                    'max' => $currentYear + 5
                ],
                'required' => false,
            ])
            ->add('language', ChoiceType::class, [
                'label' => 'product.form.language',
                'choices' => [
                    'product.form.language.french' => 'fr',
                    'product.form.language.german' => 'de',
                    'product.form.language.italian' => 'it',
                    'product.form.language.english' => 'en',
                ],
                'attr' => ['class' => 'form-select'],
                'placeholder' => 'product.form.language.placeholder',
                'required' => false,
            ])
            ->add('variant', TextType::class, [
                'label' => 'product.form.variant',
                'attr' => [
                    'placeholder' => 'product.form.variant.placeholder',
                    'class' => 'form-control'
                ],
                'required' => false,
            ])
            ->add('images', FileType::class, [
                'label' => 'product.form.images',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp,image/gif',
                    'class' => 'product-images-filepond'
                ],
                'constraints' => [
                    new All([
                        new File([
                            'maxSize' => '5M',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                                'image/gif',
                            ],
                            'mimeTypesMessage' => 'product.form.images.invalid_type',
                        ])
                    ])
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
