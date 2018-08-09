<?php

namespace App\Controller;

use App\Form\LocaleForm;
use App\Form\OrderTypeEdit;
use App\Form\OrderTypeNew;
use App\Entity\User;
use App\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class OrderController extends AbstractController
{
    /**
     * @Route("/", name="order_list")
     * @Method({"GET"})
     */
    public function index(Request $request)
    {
        $orders = $this->getDoctrine()->getRepository(Order::class)->findBy([
            'user' => $this->getUser()
        ]);

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
        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());
        $order = new Order();
        $priceController = new PriceController();
        $form = $this->createForm(OrderTypeNew::class, $order);
        $form->get('name')->setData($user->getUsername());
        $form->handleRequest($request);

        $time = new \DateTime();
        $order->setDate($time);

        $order->setUser($user);

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
        $priceController = new PriceController();
        $order = $this->getDoctrine()->getRepository(Order::class)->find($id);

        $form = $this->createForm(OrderTypeEdit::class, $order);

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
            $entityManager->persist($order);
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
