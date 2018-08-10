<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 09/08/2018
 * Time: 20:24
 */

namespace App\Controller;


use App\Form\LocaleForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LocaleController extends AbstractController
{
//    /**
//     * @Route("/changeLocale", name="changeLocale" )
//     */
//    public function changeLocale(Request $request)
//    {
//        $user = $this->getUser();
//        $form = $this->createForm(LocaleForm::class);
//
//        $form->handleRequest($request);
//
//        if($form->isSubmitted() && $form->isValid())
//        {
//            $entityManager = $this->getDoctrine()->getManager();
//            $locale = $form->getData();
//            $userLocale=$user->setLocale($locale);
//            $entityManager->persist($userLocale);
//            $entityManager->flush();
//        }
//
//        return $this->render('orders/locale.html.twig', [
//            'form' => $form->createView()
//        ]);
//    }
//                                                     dlaczego po przeniesieniu formularza changeLocale nie działało?
//                    i działa tylko gdy w bazie jest pl_PL, en_EN itp. a LanguageType przypisuje wartości pl, en  itd.
    /**
     * @Route("/changeLocale", name="changeLocale" )
     */
    public function changeLocale(Request $request)
    {
        $form = $this->createFormBuilder(null)
            ->add('locale', LanguageType::class,[
                'preferred_choices' => ['pl','en']
    ])
            ->add('save', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $locale = $form->getData()['locale']."_".strtoupper($form->getData()['locale']);
            $user = $this->getUser();
            $user->setLocale($locale);
            $em->persist($user);
            $em->flush();
        }
        return $this->render('orders/locale.html.twig', [
            'form' => $form->createView()
        ]);
    }
}