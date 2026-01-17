<?php

namespace App\Form;

use App\Entity\Rack;
use App\Entity\Warehouse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class RackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'form.name',
                'attr' => ['class' => 'form-control']
            ])
//            ->add('image', FileType::class, [
//                'label' => 'rack.new.title',
//                'mapped' => false,
//                'required' => false,
//                'multiple' => true,
//                'attr' => [
//                    'accept' => 'image/jpeg,image/png,image/webp,image/gif',
//                    'class' => 'form-control'
//                ],
//                'constraints' => [
//                    new All([
//                        new File([
//                            'maxSize' => '5M',
//                            'mimeTypes' => [
//                                'image/jpeg',
//                                'image/png',
//                                'image/webp',
//                                'image/gif',
//                            ],
//                            'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, WebP, GIF)',
//                        ])
//                    ])
//                ],
//                'help' => 'Formats acceptés: JPEG, PNG, WebP, GIF. Taille max: 5MB par image'
//            ])
            ->add('address', TextareaType::class, [
                'label' => 'rack.table.address',
                'attr' => [
                    'placeholder' => 'rack.address.placeholder',
                    'class' => 'form-control',
                    'rows' => 3
                ],
                'required' => false,
            ])
            ->add('warehouse', EntityType::class, [
                'label' => 'nav.warehouses',
                'class' => Warehouse::class,
                'choice_label' => 'name',
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
