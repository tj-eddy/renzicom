<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'user.form.name.label',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'user.form.name.placeholder',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'user.form.email.label',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'user.form.email.placeholder',
                ],
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'user.form.role.label',
                'choices' => [
                    'role.admin' => 'ROLE_ADMIN',
                    'role.driver' => 'ROLE_DRIVER',
                    'role.statistics' => 'ROLE_STATISTICS',
                ],
                'attr' => ['class' => 'form-select'],
                'placeholder' => 'user.form.role.placeholder',
                'required' => true,
            ])
            ->add('password', PasswordType::class, [
                'label' => 'user.form.password.label',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'new-password',
                    'placeholder' => 'user.form.password.placeholder',
                ],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'user.form.is_active.label',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('avatarFile', FileType::class, [
                'label' => 'user.form.avatar.label',
                'mapped' => false,
                'required' => false,
                'help' => 'user.form.avatar.help',
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*',
                ],
                'constraints' => [
                    new File(maxSize: '2M', mimeTypes: [
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                        'image/webp',
                    ], mimeTypesMessage: 'validation.user.avatar.invalid_format'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
