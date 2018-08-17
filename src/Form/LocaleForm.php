<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 08/08/2018
 * Time: 11:23
 */

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class LocaleForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $form, array $options)
    {
        $form
            ->add('locale', LanguageType::class)  // jak tłumaczyć labele formularzy?
            ->add('save', SubmitType::class);
    }
                                                            // nie działa gdy wywoływany LocaleForm, dlaczego?
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
        ));
    }
}