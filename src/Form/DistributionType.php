<?php

namespace App\Form;

use App\Entity\Distribution;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Warehouse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DistributionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('warehouse', EntityType::class, [
                'class' => Warehouse::class,
                'choice_label' => 'name',
                'label' => 'distribution.form.warehouse.label',
                'required' => true,
                'placeholder' => 'distribution.form.warehouse.placeholder',
                'mapped' => false, // Non mappé car c'est juste pour la sélection
                'attr' => [
                    'class' => 'form-select',
                    'data-warehouse-select' => 'true',
                ],
            ])
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
            ->add('status', ChoiceType::class, [
                'label' => 'distribution.form.status.label',
                'required' => true,
                'choices' => [
                    'distribution.status.preparing' => Distribution::STATUS_PREPARING,
                    'distribution.status.in_progress' => Distribution::STATUS_IN_PROGRESS,
                    'distribution.status.delivered' => Distribution::STATUS_DELIVERED,
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

        // Formulaire dynamique pour product et quantity
        $formModifier = function (FormInterface $form, ?Warehouse $warehouse = null) {
            $products = null === $warehouse ? [] : $warehouse->getAvailableProducts();

            $form->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => function (Product $product) use ($warehouse) {
                    if (null === $warehouse) {
                        return $product->getName();
                    }

                    // Afficher le nom + quantité disponible
                    $stock = $warehouse->getStockForProduct($product);
                    $quantity = $stock ? $stock->getQuantity() : 0;

                    return sprintf('%s (Stock: %d)', $product->getName(), $quantity);
                },
                'label' => 'distribution.form.product.label',
                'required' => true,
                'placeholder' => null === $warehouse
                    ? 'distribution.form.product.select_warehouse_first'
                    : 'distribution.form.product.placeholder',
                'choices' => $products,
                'disabled' => null === $warehouse,
                'attr' => [
                    'class' => 'form-select',
                    'data-product-select' => 'true',
                ],
            ]);

            $form->add('quantity', IntegerType::class, [
                'label' => 'distribution.form.quantity.label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'distribution.form.quantity.placeholder',
                    'min' => 1,
                    'data-quantity-input' => 'true',
                ],
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();
                $formModifier($event->getForm(), null);
            }
        );

        $builder->get('warehouse')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $warehouse = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $warehouse);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Distribution::class,
        ]);
    }
}
