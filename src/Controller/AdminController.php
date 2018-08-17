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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(Request $request)
    {
        $orders = $this->getDoctrine()->getRepository(Order::class)->findAll();

        $locale = $this->getUser()->getLocale();

        $currency = "$";
        $totalPrice = 0;
        foreach ($orders as $order)
        {
            $totalPrice += $order->getPrice();
        }

        if($locale == "pl_PL" || $locale == "pl")
        {
            $totalPrice *= 4;
            $currency = "PLN";
        }

        return $this->render("admin/index.html.twig", array(
            'orders' => $orders,
            'totalPrice' => $totalPrice,
            'currency' => $currency
        ));
    }

    /**
     * @Route("/admin/edit/{id}", name="admin_edit_order")
     */
    public function edit(Request $request, $id)
    {
        $priceController = new PriceController();
        $order = $this->getDoctrine()->getRepository(Order::class)->find($id);

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

        $results=$orderRepository->findByExampleField($q);

        $locale = $this->getUser()->getLocale();

        $currency = "$";
        $totalPrice = 0;
        foreach ($results as $result)
        {
            $totalPrice += $result->getPrice();
        }

        if($locale == "pl_PL" || $locale == "pl")
        {
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
     * @Route("/admin/delete_user/{slug}", name="admin/delete_user")
     * @Method({"DELETE"})
     */
    public function delete(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->findOneBy([
            'username' => $slug
        ]);

        if(!empty($user)){
            $orders = $em->getRepository(Order::class)->findBy([
            'name' => $slug
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
        } else if (empty($user)){
            $response = new Response();
            $response->send();

            $error = "Unknown user.";

            $orders = $this->getDoctrine()->getRepository(Order::class)->findAll();
            return $this->render('admin/index.html.twig',[
                'orders' => $orders,
                'error' => $error
            ]);
        }
    }
}
