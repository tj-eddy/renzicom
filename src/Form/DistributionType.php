<?php

namespace App\Form;

use App\Entity\Distribution;
use App\Entity\Product;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DistributionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getName().' ('.$user->getEmail().')';
                },
                'label' => 'distribution.form.user.label',
                'required' => true,
                'placeholder' => 'distribution.form.user.placeholder',
                'attr' => [
                    'class' => 'form-select',
                ],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('u')
                        ->where('u.role = :role OR u.role LIKE :roleDriver')
                        ->setParameter('role', 'ROLE_DRIVER')
                        ->setParameter('roleDriver', '%ROLE_DRIVER%')
                        ->orderBy('u.name', 'ASC');
                },
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'label' => 'distribution.form.product.label',
                'required' => true,
                'placeholder' => 'distribution.form.product.placeholder',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'distribution.form.quantity.label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'distribution.form.quantity.placeholder',
                    'min' => 1,
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'distribution.form.status.label',
                'required' => true,
                'choices' => [
                    'distribution.statuses.preparing' => Distribution::STATUS_PREPARING,
                    'distribution.statuses.in_progress' => Distribution::STATUS_IN_PROGRESS,
                    'distribution.statuses.delivered' => Distribution::STATUS_DELIVERED,
                    'distribution.statuses.cancelled' => Distribution::STATUS_CANCELLED,
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('destination', TextareaType::class, [
                'label' => 'distribution.form.destination.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'distribution.form.destination.placeholder',
                    'rows' => 5,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Distribution::class,
        ]);
    }
}
