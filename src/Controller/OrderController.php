<?php

namespace App\Controller;

use App\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class OrderController extends AbstractController
{
    /**
     * @Route("/", name="order_list")
     * @Method({"GET"})
     */
    public function index()
    {
        $orders = $this->getDoctrine()->getRepository(Order::class)->findAll();
//        $orders = array(
//            "name" => "hi",
//            "cpu" => "i3",
//            "ram" => 8,
//            "drive" => 128,
//            "screen" => 13,
//            "price" => 2500
//        );
        return $this->render("orders/index.html.twig", array(
            'orders' => $orders
        ));
    }

    /**
     * @Route("/order/new", name="new_order")
     * @Method({"GET","POST"})
     */
    public function new(Request $request)
    {
        $order = new Order();
        $priceController = new PriceController();

        $form = $this->createFormBuilder($order)
            ->add('name', TextType::class, array('attr' =>
                array('class' => 'form-control')))
//            ->add('cpu', ChoiceType::class, array(
//                'attr' => array('class' => 'form-control'),
//                'choices'  => array(
//                    'none' => '0',$order->setPrice(($order->getPrice())),
//                    'i3' => 'i3',$order->setPrice(($order->getPrice())+200),
//                    'i5' => 'i5',$order->setPrice(($order->getPrice())+400),
//                    'i7' => 'i7',$order->setPrice(($order->getPrice())+600)
//                )
//            ))
            ->add('cpu', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'choices'  => array(
                    'none' => null,
                    'i3' => 'i3',
                    'i5' => 'i5',
                    'i7' => 'i7',
                )
            ))
            ->add('ram', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'choices'  => array(
                    'none' => null,
                    '8' => 8,
                    '16' => 16,
                    '32' => 32,
                )
            ))
            ->add('drive', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'choices'  => array(
                    'none' => null,
                    '128' => 128,
                    '256' => 256,
                    '512' => 512,
                )
            ))
            ->add('screen', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'choices'  => array(
                    'none' => null,
                    '10' => 10,
                    '13' => 13,
                    '15' => 15,
                )
            ))
            ->add('price', TextType::class, array(
                'attr' => array('class' => 'form-control'),
                //'data' => $order->getPrice(),
                'disabled' => 'true'
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Create',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ))
            ->getForm();

        $form->handleRequest($request);

        $time = new \DateTime();
        $order->setDate($time);

        $order->setPrice($priceController->calculate(
            $order->getCpu(),
            $order->getRam(),
            $order->getDrive(),
            $order->getScreen()
        ));


        if ($form->isSubmitted() && $form->isValid()) {
            $order = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute('order_list');
        }

        return $this->render('orders/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/order/edit/{id}", name="edit_order")
     * @Method({"GET","POST"})
     */
    public function edit(Request $request, $id)
    {
        $order = new Order();
        $priceController = new PriceController();
        $order = $this->getDoctrine()->getRepository(Order::class)->find($id);

        $form = $this->createFormBuilder($order)
            ->add('name', TextType::class, array('attr' =>
                array('class' => 'form-control')))
//            ->add('cpu', ChoiceType::class, array(
//                'attr' => array('class' => 'form-control'),
//                'choices'  => array(
//                    'none' => '0',$order->setPrice(($order->getPrice())),
//                    'i3' => 'i3',$order->setPrice(($order->getPrice())+200),
//                    'i5' => 'i5',$order->setPrice(($order->getPrice())+400),
//                    'i7' => 'i7',$order->setPrice(($order->getPrice())+600)
//                )
//            ))
            ->add('cpu', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'choices'  => array(
                    'none' => null,
                    'i3' => 'i3',
                    'i5' => 'i5',
                    'i7' => 'i7',
                )
            ))
            ->add('ram', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'choices'  => array(
                    'none' => null,
                    '8' => 8,
                    '16' => 16,
                    '32' => 32,
                )
            ))
            ->add('drive', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'choices'  => array(
                    'none' => null,
                    '128' => 128,
                    '256' => 256,
                    '512' => 512,
                )
            ))
            ->add('screen', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'choices'  => array(
                    'none' => null,
                    '10' => 10,
                    '13' => 13,
                    '15' => 15,
                )
            ))
            ->add('price', TextType::class, array(
                'attr' => array('class' => 'form-control'),
                //'data' => $order->getPrice(),
                'disabled' => 'true'
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Create',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ))
            ->getForm();

        $form->handleRequest($request);

        $time = new \DateTime();
        $order->setDate($time);

        $order->setPrice($priceController->calculate(
            $order->getCpu(),
            $order->getRam(),
            $order->getDrive(),
            $order->getScreen()
        ));

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('order_list');
        }

        return $this->render('orders/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/order/{id}",name="order_show")
     * @
     */
    public function show($id)
    {
        $order = $this->getDoctrine()->getRepository(Order::class)->find($id);

        return $this->render('orders/show.html.twig', array
        (
            'order' => $order
        ));
    }

    /**
     * @Route("/order/delete/{id}")
     * @Method({"DELETE"})
     */
    public function delete(Request $request, $id)
    {
        $order = $this->getDoctrine()->getRepository(Order::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($order);
        $entityManager->flush();

        $response = new Response();
        $response->send();
    }
}
