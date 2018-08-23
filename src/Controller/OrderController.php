<?php

namespace App\Controller;

use App\Form\LocaleForm;
use App\Form\OrderTypeEdit;
use App\Form\OrderTypeNew;
use App\Entity\User;
use App\Entity\Order;
use App\Form\UserDataEditType;
use App\Repository\OrderRepository;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Vich\UploaderBundle\Form\Type\VichImageType;

class OrderController extends Controller
{
    /**
     * @Route("/", name="order_list")
     * @Method({"GET"})
     */
    public function index(Request $request, OrderRepository $orderRepository)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());

        if(!empty($user))
        {
            $orders = $this->getDoctrine()->getRepository(Order::class)->findBy([
                'user' => $user
            ]);
        } else {
            $orders = null;
        }
        $locale = $this->getUser()->getLocale();

        $currency = "$";
        $totalPrice = 0;
        foreach ($orders as $order) {
            $totalPrice += $order->getPrice();
            if ($locale == "pl_PL" || $locale == "pl") {
                //$order->setPrice($this->convertCurrency($order->getPrice(), 'USD', 'PLN')); // ZA DUŻO REQUESTÓW FREE
                $order->setPrice($order->getPrice() * 3.8);
            }
        }

        if ($locale == "pl_PL" || $locale == "pl") {
            $totalPrice *= 3.8;
            //$totalPrice = $this->convertCurrency($totalPrice, 'USD', 'PLN');
            $currency = "PLN";
        }

        // tutaj zaczyna się polska szpachla Janusza
        $_user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());

//        try {
//            $userId = $_user->getUser()->getId();
//        } catch (NotNullConstraintViolationException $e){
//            $e->getMessage();
//        }

            $userId = $_user->getId();

        $qb = $orderRepository->createQueryBuilder('o')
            ->setParameter('q', '' . $userId . '')
            ->andWhere('o.user = :q');

        $query = $qb->getQuery(); //    ->getResult();

        $paginator = $this->get('knp_paginator');
        $orders = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $this->render("orders/index.html.twig", array(
            'user' => $user,
            'orders' => $orders,
            'totalPrice' => $totalPrice,
            'currency' => $currency
        ));
    }

    /**
     * @Route("/order/new", name="new_order")
     * @Method({"GET","POST"})
     */
    public function new(Request $request, \Swift_Mailer $mailer)
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

            $message = (new \Swift_Message('Order accepted ' . ucfirst($user->getUsername()) . '!'))
                ->setFrom('petermailer777@gmail.com')
                ->setTo($user->getEmail())
//                ->setTo("ochenx@gmail.com")
                ->setBody(
                    'Hello ' . ucfirst($user->getUsername()) . '! ' .
                    'Your order has been accepted, you will be notified when the order will be sent.
                Your order:
                     cpu: ' . $order->getCpu() .
                    ' ram: ' . $order->getRam() .
                    ' drive: ' . $order->getDrive() .
                    ' screen:' . $order->getScreen() .
                    ' price:' . $order->getPrice()
                );
            $mailer->send($message);

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

//        $time = new \DateTime();
//        $order->setDate($time);

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
     */
    public function show($id)
    {
        $order = $this->getDoctrine()->getRepository(Order::class)->find($id);

        $locale = $this->getUser()->getLocale();

        $currency = "$";
        $orderPrice = $order->getPrice();

        if ($locale == "pl_PL" || $locale == "pl") {
            $orderPrice = $this->convertCurrency($orderPrice, 'USD', 'PLN');
            $currency = "PLN";
        }

        return $this->render('orders/show.html.twig', array
        (
            'order' => $order,
            'orderPrice' => $orderPrice,
            'currency' => $currency
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

    public function convertCurrency($amount, $from_currency, $to_currency)
    {

        $from_Currency = urlencode($from_currency);
        $to_Currency = urlencode($to_currency);
        $query = "{$from_Currency}_{$to_Currency}";

        $json = file_get_contents("https://free.currencyconverterapi.com/api/v6/convert?q={$query}&compact=y");
        $obj = json_decode($json, true);
        $val = floatval($obj["USD_PLN"]["val"]);

        $total = $val * $amount;
        return number_format($total, 2, '.', '');
    }

    /**
     * @Route("/about", name="about")
     */
    public function about()
    {
        return $this->render('about.html.twig');
    }

    /**
     * @Route("/editProfile", name="editProfile")
     */
    public function editProfile(Request $request)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());
        $user->setPlainPassword(0);  // JAKI INNY SPOSÓB NA BŁĄD : PLAINPASSWORD CANT BE NULL ?

        $form1 = $this->createForm(UserDataEditType::class, $user);
        $form1->handleRequest($request);

        if ($form1->isSubmitted() && $form1->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('order_list');
        }

        return $this->render('orders/userEdit.html.twig', [
            'form' => $form1->createView()
        ]);
    }

//    /**
//     * @Route("/editProfile", name="editPhoto")
//     */
//    public function editPhoto(Request $request)
//    {
//        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());
//        $user->setPlainPassword(0);  // JAKI INNY SPOSÓB NA BŁĄD : PLAINPASSWORD CANT BE NULL ?
//
//        $form2 = $this->createForm(VichImageType::class, $user);
//        $form2->handleRequest($request);
//
//        if ($form2->isSubmitted() && $form2->isValid()) {
//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->persist($user);
//            $entityManager->flush();
//            return $this->redirectToRoute('order_list');
//        }
//
//        return $this->render('orders/userEdit.html.twig',[
//            'form2' => $form->createView()
//        ]);
//    }
}
