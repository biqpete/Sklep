<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 08/08/2018
 * Time: 14:10
 */

namespace App\Controller;

use App\Entity\Order;
use App\Form\AdminOrderTypeEdit;
use App\Repository\OrderRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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

        return $this->render("admin/index.html.twig", array(
            'orders' => $orders
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

        return $this->render('admin/index.html.twig', [
            'orders' => $results,
            'q' => $q
        ]);
    }

}
