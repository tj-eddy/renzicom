<?php

namespace App\Form;

use App\Entity\Warehouse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

/**
 * Warehouse form type
 */
class WarehouseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'entrepôt',
                'attr' => [
                    'placeholder' => 'Entrez le nom de l\'entrepôt',
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'placeholder' => 'Entrez l\'adresse complète',
                    'class' => 'form-control',
                    'rows' => 3
                ],
                'required' => false,
            ])
            ->add('images', FileType::class, [
                'label' => 'Images de l\'entrepôt',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp,image/gif',
                    'class' => 'form-control'
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
                            'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, WebP, GIF)',
                        ])
                    ])
                ],
                'help' => 'Formats acceptés: JPEG, PNG, WebP, GIF. Taille max: 5MB par image'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Warehouse::class,
        ]);
    }
}
