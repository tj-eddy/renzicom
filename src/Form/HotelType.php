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
                'label' => 'hotel.form.name.label',
                'attr' => [
                    'placeholder' => 'hotel.form.name.placeholder',
                    'class' => 'form-control'
                ],
                'required' => true
            ])
            ->add('address', TextType::class, [
                'label' => 'hotel.form.address.label',
                'attr' => [
                    'placeholder' => 'hotel.form.address.placeholder',
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('contactName', TextType::class, [
                'label' => 'hotel.form.contact_name.label',
                'attr' => [
                    'placeholder' => 'hotel.form.contact_name.placeholder',
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('contactEmail', EmailType::class, [
                'label' => 'hotel.form.contact_email.label',
                'attr' => [
                    'placeholder' => 'hotel.form.contact_email.placeholder',
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('contactPhone', TelType::class, [
                'label' => 'hotel.form.contact_phone.label',
                'attr' => [
                    'placeholder' => 'hotel.form.contact_phone.placeholder',
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
