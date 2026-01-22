<?php

namespace App\Form;

use App\Entity\Distribution;
use App\Entity\Intervention;
use App\Entity\Rack;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterventionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantityAdded')
            ->add('photoBefore')
            ->add('photoAfter')
            ->add('notes')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('distribution', EntityType::class, [
                'class' => Distribution::class,
                'choice_label' => 'id',
            ])
            ->add('rack', EntityType::class, [
                'class' => Rack::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
        ]);
    }
}
