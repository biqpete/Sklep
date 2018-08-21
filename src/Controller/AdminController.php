<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 08/08/2018
 * Time: 14:10
 */

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Form\AdminOrderTypeEdit;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;



class AdminController extends AbstractController //Controller implements PaginatorAwareInterface
{
//    /**
//     * @Route("/adminlogin", name="adminlogin")
//     */
//    public function login(Request $request, AuthenticationUtils $utils)
//    {
//        $error = $utils->getLastAuthenticationError();
//        $lastUserName = $utils->getLastUsername();
//
//        return $this->render('security/adminlogin.html.twig', [
//            'error' => $error,
//            'last_username' => $lastUserName
//        ]);
//    }

    /**
     * @Route("/admin", name="admin")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(Request $request, OrderRepository $orderRepository) : Response//, PaginatorInterface $paginator)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findAll();
        $orders = $this->getDoctrine()->getRepository(Order::class)->findAll();

        $locale = $this->getUser()->getLocale();

        $currency = "$";
        $totalPrice = 0;
        foreach ($orders as $order) {
            $totalPrice += $order->getPrice();
        }

        if ($locale == "pl_PL" || $locale == "pl") {
            $totalPrice *= 4;
            $currency = "PLN";
        }

        return $this->render("admin/index.html.twig", array(
            'user' => $user,
            'orders' => $orders,
            'totalPrice' => $totalPrice,
            'currency' => $currency
        ));

//        $em = $this->getDoctrine()->getManager();
//        $q = new QueryBuilder($em);
//        $qb = $request->query->get('q', '');
//        $query = $orderRepository->createQueryBuilder('b');
//        if (!empty($q)) {
//            $query->where('b.title LIKE :q')
//                ->setParameter('q', '%'.$q.'%');
//        }
//        $query = $query->getQuery();
//
//        $paginator  = $this->get('knp_paginator');
//        $pagination = $paginator->paginate(
//            $query, /* query NOT result */
//            $request->query->getInt('page', 1)/*page number*/,
//            10/*limit per page*/
//        );
//
//        return $this->render('index.html.twig', [
//            'books' => $pagination,
//            'q' => $q
//        ]);

//        $paginator = $this->get('knp_paginator');
//        $orders = $paginator->paginate(
//            $query = $this->getDoctrine()->getRepository(Order::class)->findAll(),
//            $request->query->getInt('page', 1), /*page number*/
//            10 /*limit per page*/
//        );
//
//        return $this->render("admin/indexPAGINATOR.html.twig", array(
//            'user' => $user,
//            'orders' => $orders,
//            'totalPrice' => $totalPrice,
//            'currency' => $currency
//        ));
    }

    /**
     * @Route("/admin/edit/{id}", name="admin_edit_order")
     */
    public function edit(Request $request, $id, \Swift_Mailer $mailer)
    {
        $priceController = new PriceController();
        $order = $this->getDoctrine()->getRepository(Order::class)->find($id);
        $user_name = $order->getName();
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'username' => $user_name
        ]);
        $username = $user->getUsername();
        $orderStatusOrigial = $order->getIsSent();
        $form = $this->createForm(AdminOrderTypeEdit::class, $order);

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
            $entityManager->flush();

            if($order->getIsSent() != $orderStatusOrigial)
            {
                if($order->getIsSent() == true)
                {
                    $message = (new \Swift_Message('Hello '.ucfirst($username).'!'))
                        ->setFrom('petermailer777@gmail.com')
                        ->setTo($user->getEmail())
                        ->setBody("Hello ".ucfirst($username).'! '.
                            "Your order from Peter's Shop has been sent!")
                    ;
                    $mailer->send($message);
                }
            }
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/admin/results", name="admin_results")
     * @IsGranted("ROLE_ADMIN")
     */
    public function search(Request $request, OrderRepository $orderRepository): Response
    {
        $q = $request->query->get('q', '');

        $results = $orderRepository->findByExampleField($q);

        $locale = $this->getUser()->getLocale();

        $currency = "$";
        $totalPrice = 0;
        foreach ($results as $result) {
            $totalPrice += $result->getPrice();
        }

        if ($locale == "pl_PL" || $locale == "pl") {
            $totalPrice *= 4;
            $currency = "PLN";
        }

//        if(!empty($results))
//        {
//            foreach ($results as $result)
//            {
//                $totalPrice = 0;
//                $totalPrice += $result->getPrice();
//            }
//        }

        return $this->render('admin/index.html.twig', [
            'orders' => $results,
            'q' => $q,
            'totalPrice' => $totalPrice,
            'currency' => $currency
        ]);
    }

    /**
     * @Route("/admin/delete_user/{id}", name="admin/delete_user")
     * @Method({"DELETE"})
     */
    public function delete(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $id
        ]);

        if (!empty($user)) {
            $orders = $em->getRepository(Order::class)->findBy([
                'user' => $id
            ]);
            foreach ($orders as $order) {
                $em->remove($order);
                $em->flush();
            }

            $em->remove($user);
            $em->flush();

            $response = new Response();
            $response->send();

            $orders = $this->getDoctrine()->getRepository(Order::class)->findAll();
//            return $this->render('admin/index.html.twig',[
//                'orders' => $orders,
//            ]);
            return $this->redirectToRoute('admin');
        } else if (empty($user)) {
            $response = new Response();
            $response->send();

            $error = "Unknown user.";

            $orders = $this->getDoctrine()->getRepository(Order::class)->findAll();
            return $this->render('admin/index.html.twig', [
                'orders' => $orders,
                'error' => $error
            ]);
        }
    }

    /**
     * @Route("/admin/show/{id}",name="admin_order_show")
     * @
     */
    public function show($id)
    {
        $order = $this->getDoctrine()->getRepository(Order::class)->find($id);

        return $this->render('admin/show.html.twig', array
        (
            'order' => $order
        ));
    }

    public function setPaginator(Paginator $paginator)
    {
        // TODO: Implement setPaginator() method.
    }
}
