<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 21/08/2018
 * Time: 14:48
 */

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;
use Vich\UploaderBundle\Form\Type\VichFileType;

class UserDataEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class,[
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('secondName', TextType::class,[
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('imageFile', VichFileType::class, array(
                'label' => 'Image',
                'required' => false,
                'allow_delete' => true,
                'download_label' => true
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Save',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
        ));
    }
}