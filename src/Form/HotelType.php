<?php

namespace App\Form;

use App\Entity\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'hotel.form.name.label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'hotel.form.name.placeholder',
                ],
            ])
            ->add('address', TextareaType::class, [
                'label' => 'hotel.form.address.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'hotel.form.address.placeholder',
                    'rows' => 3,
                ],
            ])
            ->add('contactName', TextType::class, [
                'label' => 'hotel.form.contact_name.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'hotel.form.contact_name.placeholder',
                ],
            ])
            ->add('contactEmail', EmailType::class, [
                'label' => 'hotel.form.contact_email.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'hotel.form.contact_email.placeholder',
                ],
            ])
            ->add('contactPhone', TelType::class, [
                'label' => 'hotel.form.contact_phone.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'hotel.form.contact_phone.placeholder',
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
