<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Supplier;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class , [
                'attr'=> [
                    'class'=>'form-control'
                    ]
                ])
            ->add('price', TextType::class , [
                'attr'=> [
                    'class'=>'form-control'
                    ]
                ])
            ->add('quantity', NumberType::class , [
                'attr'=> [
                    'class'=>'form-control'
                    ]
                ])
            ->add('category',EntityType::class , [
                'attr'=> [
                    'class'=>'form-control'
                ],
                'required' => false,
                'placeholder' => '',
                'class' => Category::class,
                'choice_label' => 'name',
            ])
            ->add('supplier',EntityType::class , [
                'attr'=> [
                    'class'=>'form-control'
                ],
                'required' => false,
                'placeholder' => '',
                'class' => Supplier::class,
                'choice_label' => 'name',
            ])
            // ->add('orders')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
