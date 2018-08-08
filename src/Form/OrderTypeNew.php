<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 08/08/2018
 * Time: 09:18
 */

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class OrderTypeNew extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        ->add('name', HiddenType::class, array(
        'attr' => [
        'class' => 'form-control',
        ],
        'data' => $user->getUsername(),//." ".$result,
        ))
        ->add('order_name', TextType::class,[
        'required' => 'true',
        'attr' => [
        'class' => 'form-control'
        ]
        ])
        ->add('cpu', ChoiceType::class, array(
        'attr' => array('class' => 'form-control'),
        'choices' => array(
        'none' => null,
        'i3' => 'i3',
        'i5' => 'i5',
        'i7' => 'i7',
        )
        ))
        ->add('ram', ChoiceType::class, array(
        'attr' => array('class' => 'form-control'),
        'choices' => array(
        'none' => null,
        '8' => 8,
        '16' => 16,
        '32' => 32,
        )
        ))
        ->add('drive', ChoiceType::class, array(
        'attr' => array('class' => 'form-control'),
        'choices' => array(
        'none' => null,
        '128' => 128,
        '256' => 256,
        '512' => 512,
        )
        ))
        ->add('screen', ChoiceType::class, array(
        'attr' => array('class' => 'form-control'),
        'choices' => array(
        'none' => null,
        '10' => 10,
        '13' => 13,
        '15' => 15,
        )
        ))
        ->add('price', TextType::class, array(
        'attr' => array('class' => 'form-control'),
        'data' => $order->getPrice(),
        'disabled' => 'true'
        ))
        ->add('date', HiddenType::class, [
        'attr' => ['class' => 'form-control']
        ])
        ->add('save', SubmitType::class, array(
        'label' => 'Create',
        'attr' => array('class' => 'btn btn-primary mt-3')
        ))
        ->getForm();
    }
}