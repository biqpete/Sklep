<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 06/08/2018
 * Time: 12:46
 */

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'attr' => [
                    'placeholder' => 'Your username',
                ]
            ])
            ->add('email', EmailType::class,[
                'attr' => [
                    'placeholder' => 'Your email'
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => array(
                    'attr' => [
                        'placeholder' => 'Your password'
                    ],
                    'label' => 'Password'
                ),
                'second_options' => array(
                    'attr' => [
                        'placeholder' => 'Repeat your password'
                    ],
                    'label' => 'Repeat Password'
                ),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
        ));
    }
}