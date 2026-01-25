<?php

namespace App\Form;

use App\Entity\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'hôtel',
                'attr' => [
                    'placeholder' => 'Ex: Hôtel Carlton',
                    'class' => 'form-control'
                ],
                'required' => true
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'placeholder' => 'Adresse complète',
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('contactName', TextType::class, [
                'label' => 'Nom du contact',
                'attr' => [
                    'placeholder' => 'Nom du responsable',
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('contactEmail', EmailType::class, [
                'label' => 'Email du contact',
                'attr' => [
                    'placeholder' => 'email@exemple.com',
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('contactPhone', TelType::class, [
                'label' => 'Téléphone du contact',
                'attr' => [
                    'placeholder' => '+261 34 00 000 00',
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('displays', CollectionType::class, [
                'entry_type' => DisplayEmbeddedType::class,
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
            'data_class' => Hotel::class,
        ]);
    }
}
