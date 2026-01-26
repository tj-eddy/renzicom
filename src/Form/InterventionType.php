<?php

namespace App\Form;

use App\Entity\Distribution;
use App\Entity\Intervention;
use App\Entity\Rack;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

use Symfony\Contracts\Translation\TranslatorInterface;

class InterventionType extends AbstractType
{
    public function __construct(
        private TranslatorInterface $translator
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isAdmin = $options['is_admin'];
        $currentUser = $options['current_user'];

        $builder
            ->add('distribution', EntityType::class, [
                'class' => Distribution::class,
                'choice_label' => function (Distribution $distribution) {
                    return sprintf(
                        '#%d - %s - %s',
                        $distribution->getId(),
                        $distribution->getProduct() ? $distribution->getProduct()->getName() : $this->translator->trans('common.na'),
                        $distribution->getUser() ? $distribution->getUser()->getName() : $this->translator->trans('common.na')
                    );
                },
                'label' => 'intervention.form.distribution.label',
                'required' => true,
                'placeholder' => 'intervention.form.distribution.placeholder',
                'attr' => [
                    'class' => 'form-select',
                ],
                'query_builder' => function ($repository) use ($isAdmin, $currentUser) {
                    $qb = $repository->createQueryBuilder('d')
                        ->where('d.status IN (:statuses)')
                        ->setParameter('statuses', ['preparing', 'in_progress']);

                    // Si ce n'est pas un admin, filtrer par utilisateur connecté
                    if (!$isAdmin && $currentUser) {
                        $qb->andWhere('d.user = :user')
                           ->setParameter('user', $currentUser);
                    }

                    return $qb->orderBy('d.createdAt', 'DESC');
                },
            ])
            ->add('rack', EntityType::class, [
                'class' => Rack::class,
                'choice_label' => function (Rack $rack) {
                    $label = $rack->getName();

                    if ($rack->getDisplay()) {
                        $label .= ' - ' . $rack->getDisplay()->getName();

                        if ($rack->getDisplay()->getHotel()) {
                            $label .= ' (' . $rack->getDisplay()->getHotel()->getName() . ')';
                        }
                    }

                    if ($rack->getProduct()) {
                        $label .= ' - ' . $rack->getProduct()->getName();
                    }

                    return $label;
                },
                'label' => 'intervention.form.rack.label',
                'required' => true,
                'placeholder' => 'intervention.form.rack.placeholder',
                'attr' => [
                    'class' => 'form-select',
                ],
                'group_by' => function (Rack $rack) {
                    if ($rack->getDisplay() && $rack->getDisplay()->getHotel()) {
                        return $rack->getDisplay()->getHotel()->getName();
                    }

                    return $this->translator->trans('common.others');
                },
            ])
            ->add('quantityAdded', IntegerType::class, [
                'label' => 'intervention.form.quantity_added.label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'intervention.form.quantity_added.placeholder',
                    'min' => 1,
                    'class' => 'form-control',
                ],
            ])
            ->add('photoBefore', FileType::class, [
                'label' => 'intervention.form.photo_before.label',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*',
                ],
                'constraints' => [
                    new File(maxSize: '10M', mimeTypes: [
                        'image/jpeg',
                        'image/png',
                        'image/jpg',
                    ], mimeTypesMessage: 'validation.intervention.photo_before.invalid'),
                ],
            ])
            ->add('photoAfter', FileType::class, [
                'label' => 'intervention.form.photo_after.label',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*',
                ],
                'constraints' => [
                    new File(maxSize: '10M', mimeTypes: [
                        'image/jpeg',
                        'image/png',
                        'image/jpg',
                    ], mimeTypesMessage: 'validation.intervention.photo_after.invalid'),
                ],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'intervention.form.notes.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'intervention.form.notes.placeholder',
                    'rows' => 4,
                    'class' => 'form-control',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
            'is_admin' => false,
            'current_user' => null,
        ]);

        $resolver->setAllowedTypes('is_admin', 'bool');
        $resolver->setAllowedTypes('current_user', ['null', User::class]);
    }
}
