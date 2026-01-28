<?php

namespace App\Form;

use App\Entity\Display;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DisplayEmbeddedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'display.form.name.label',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'display.form.name.placeholder'
                ],
                'required' => true
            ])
            ->add('location', TextType::class, [
                'label' => 'display.form.location.label',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'display.form.location.placeholder'
                ],
                'required' => false
            ])
            ->add('racks', CollectionType::class, [
                'entry_type' => RackEmbeddedType::class,
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
            'data_class' => Display::class,
        ]);
    }
}
