<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\Product;
use PhpParser\Parser\Multiple;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('date')
            ->add('products',EntityType::class , [
                'attr'=> [
                    'class'=>'form-select'
                ],
                'required' => false,
                'placeholder' => '',
                'class' => Product::class,
                'choice_label' => 'name',
                'multiple'=>'true'
            ])
            /* ->add('totalPrice')
            ->add('facture')
            ->add('admin')
            ->add('client') */
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
