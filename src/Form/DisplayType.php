<?php

namespace App\Form;

use App\Entity\Display;
use App\Entity\Hotel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DisplayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hotel', EntityType::class, [
                'class' => Hotel::class,
                'choice_label' => 'name',
                'label' => 'display.form.hotel.label',
                'required' => true,
                'placeholder' => 'display.form.hotel.placeholder',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'display.form.name.label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'display.form.name.placeholder',
                ],
            ])
            ->add('location', TextType::class, [
                'label' => 'display.form.location.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'display.form.location.placeholder',
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
