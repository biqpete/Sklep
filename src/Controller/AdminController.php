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
use App\Form\UploadUsersForm;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use League\Csv\Reader;
use League\Csv\Statement;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminController extends Controller implements PaginatorAwareInterface
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
     * @Route("/admin/all-orders", name="adminAllOrders")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(Request $request, OrderRepository $orderRepository): Response//, PaginatorInterface $paginator)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findAll();
        $orders = $this->getDoctrine()->getRepository(Order::class)->findAll();

        $locale = $this->getUser()->getLocale();

        $currency = "$";
        $totalPrice = 0;
        foreach ($orders as $order) {
            $totalPrice += $order->getPrice();
            if ($locale == "pl_PL" || $locale == "pl") {
//                $order->setPrice($this->convertCurrency($order->getPrice(), 'USD', 'PLN')); // ZA DUŻO REQUESTÓW NA FREE
                $order->setPrice(($order->getPrice()) * (3.8));
            }
        }

        if ($locale == "pl_PL" || $locale == "pl") {
//            $totalPrice = $this->convertCurrency($totalPrice, 'USD', 'PLN');
            $totalPrice *= 3.8;
            $currency = "PLN";
        }

//        $em = $this->getDoctrine()->getManager();

        $query = $orderRepository->createQueryBuilder('o');
        $query = $query->getQuery(); // ->getResult()

        $paginator = $this->get('knp_paginator');
        $orders = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $this->render("admin/indexPAGINATOR.html.twig", array(
            'user' => $user,
            'orders' => $orders,
            'totalPrice' => $totalPrice,
            'currency' => $currency
        ));
    }

    /**
     * @Route("/admin/all-users", name="adminAllUsers")
     * @IsGranted("ROLE_ADMIN")
     */
    public function showAllUsers(Request $request, UserRepository $userRepository): Response//, PaginatorInterface $paginator)
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

//        $em = $this->getDoctrine()->getManager();

        $query = $userRepository->createQueryBuilder('o');
        $query = $query->getQuery();// ->getResult();

        $paginator = $this->get('knp_paginator');
        $users = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $this->render("admin/showAllUsers.html.twig", array(
            'users' => $users,
        ));
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

            if ($order->getIsSent() != $orderStatusOrigial) {
                if ($order->getIsSent() == true) {
                    $message = (new \Swift_Message('Hello ' . ucfirst($username) . '!'))
                        ->setFrom('petermailer777@gmail.com')
                        ->setTo($user->getEmail())
                        ->setBody("Hello " . ucfirst($username) . '! ' .
                            "Your order from Peter's Shop has been sent!");
                    $mailer->send($message);
                }
            }
            return $this->redirectToRoute('adminAllOrders');
        }

        return $this->render('admin/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/admin/results-orders", name="admin_results_orders")
     * @IsGranted("ROLE_ADMIN")
     */
    public function searchOrders(Request $request, OrderRepository $orderRepository): Response
    {
        $q = $request->query->get('q', '');
//        $results = $orderRepository->findByExampleField($q);

        $currency = "$";
        $totalPrice = 0;


        $query = $orderRepository->createQueryBuilder('o')
            ->setParameter('q', '' . $q . '')
            ->andWhere('o.name = :q');
        $query = $query->getQuery();

        $paginator = $this->get('knp_paginator');
        $orders = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        if (!empty($orders)) {
            foreach ($orders as $order) {
                $totalPrice += $order->getPrice();
            }
        }

        $locale = $this->getUser()->getLocale();
        if ($locale == "pl_PL" || $locale == "pl") {
            $totalPrice = $this->convertCurrency($totalPrice, 'USD', 'PLN');
            $currency = "PLN";
        }

        return $this->render('admin/indexPAGINATOR.html.twig', [
            'orders' => $orders,
            'totalPrice' => $totalPrice,
            'currency' => $currency
        ]);
    }

    /**
     * @Route("/admin/results-users", name="admin_results_users")
     * @IsGranted("ROLE_ADMIN")
     */
    public function searchUsers(Request $request, UserRepository $userRepository): Response
    {
        $q = $request->query->get('q', '');

        $query = $userRepository->createQueryBuilder('o')
            ->setParameter('q', '' . $q . '')
            ->andWhere('o.username = :q');

        $query = $query->getQuery()->getResult();

        $paginator = $this->get('knp_paginator');
        $user = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $this->render('admin/showAllUsers.html.twig', [
            'users' => $user
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
            return $this->redirectToRoute('adminAllUsers');
        } else if (empty($user)) {
            $response = new Response();
            $response->send();

            $error = "Unknown user.";

            $orders = $this->getDoctrine()->getRepository(Order::class)->findAll();
            return $this->render('admin/indexPAGINATOR.html.twig', [
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

    /**
     * @Route("/admin/upload-csv")
     */
    public function uploadUsers(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());
        $user->setPlainPassword(0);

        $form = $this->createForm(UploadUsersForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();


            $filename = str_replace(' ', '-', $user->getFileName());

            try {
                $readerUsers = Reader::createFromPath('../public/files/' . $filename, 'r');
                $readerUsers->setHeaderOffset(0);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash(
                    'error',
                    ''
                );
            }
            $statement = (new Statement())
                ->offset(0);

            $resultsUsers = $statement->process($readerUsers);
            foreach ($resultsUsers as $row) {
                $user = (new User())
                    ->setFirstName($row['first_name'])
                    ->setSecondName($row['second_name'])
                    ->setUsername($row['username'])
                    ->setPassword($passwordEncoder->encodePassword($user, $row['password']))
                    ->setEmail($row['email'])
                    ->setRoles([$row['roles']])
                    ->setIsActive($row['is_active']);

                    $em->persist($user);
            }
            try {
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash(
                    'error',
                    'At least one of users was already in database'
                );
            }
            $success = "Users have been imported!";
        } else {
            $success = "";
        }
        return $this->render('admin/uploadUsers.html.twig', array
        (
            'form' => $form->createView(),
            'success' => $success,
//            'error' => $error
        ));
    }

    /**
     * @Route("/admin/export-xls")
     */
    public function exportUsers(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        $spreadsheet = new Spreadsheet();  /*----Spreadsheet object-----*/
        $excel_writer = new Xls($spreadsheet);  /*----- Excel (Xls) Object*/

        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A1' , 'id')->getStyle('A1')->getFont();
        $activeSheet->setCellValue('B1' , 'username')->getStyle('B1')->getFont();
        $activeSheet->setCellValue('C1' , 'password')->getStyle('C1')->getFont();
        $activeSheet->setCellValue('D1' , 'email')->getStyle('D1')->getFont();
        $activeSheet->setCellValue('E1' , 'locale')->getStyle('E1')->getFont();
        $activeSheet->setCellValue('F1' , 'roles')->getStyle('F1')->getFont();
        $activeSheet->setCellValue('G1' , 'hash')->getStyle('G1')->getFont();
        $activeSheet->setCellValue('H1' , 'is_active')->getStyle('H1')->getFont();
        $activeSheet->setCellValue('I1' , 'first_name')->getStyle('I1')->getFont();
        $activeSheet->setCellValue('J1' , 'second_name')->getStyle('J1')->getFont();
        $activeSheet->setCellValue('K1' , 'image_name')->getStyle('K1')->getFont();
        $activeSheet->setCellValue('L1' , 'image_size')->getStyle('L1')->getFont();
        $activeSheet->setCellValue('M1' , 'updated_at')->getStyle('M1')->getFont();
        $activeSheet->setCellValue('N1' , 'file_name')->getStyle('N1')->getFont();
        $activeSheet->setCellValue('O1' , 'file_size')->getStyle('O1')->getFont();

        $b = 2;
        for($i = 0; $i< count((array)$users); $i++)
        {
            $activeSheet->setCellValue('A'.$b , $users[$i]->getId())->getStyle('A'.$b)->getFont();
            $activeSheet->setCellValue('B'.$b , $users[$i]->getUsername())->getStyle('B'.$b)->getFont();
            $activeSheet->setCellValue('C'.$b , $users[$i]->getPassword())->getStyle('C'.$b)->getFont();
            $activeSheet->setCellValue('D'.$b , $users[$i]->getEmail())->getStyle('D'.$b)->getFont();
            $activeSheet->setCellValue('E'.$b , $users[$i]->getLocale())->getStyle('E'.$b)->getFont();
            $activeSheet->setCellValue('F'.$b , serialize($users[$i]->getRoles()))->getStyle('F'.$b)->getFont();
            $activeSheet->setCellValue('G'.$b , $users[$i]->getHash())->getStyle('G'.$b)->getFont();
            $activeSheet->setCellValue('H'.$b , $users[$i]->getIsActive())->getStyle('H'.$b)->getFont();
            $activeSheet->setCellValue('I'.$b , $users[$i]->getFirstName())->getStyle('I'.$b)->getFont();
            $activeSheet->setCellValue('J'.$b , $users[$i]->getSecondName())->getStyle('J'.$b)->getFont();
            $activeSheet->setCellValue('K'.$b , $users[$i]->getImageName())->getStyle('K'.$b)->getFont();
            $activeSheet->setCellValue('L'.$b , $users[$i]->getImageSize())->getStyle('L'.$b)->getFont();
            $activeSheet->setCellValue('M'.$b , $users[$i]->getUpdatedAt())->getStyle('M'.$b)->getFont();
            $activeSheet->setCellValue('N'.$b , $users[$i]->getFileName())->getStyle('N'.$b)->getFont();
            $activeSheet->setCellValue('O'.$b , $users[$i]->getFileSize())->getStyle('O'.$b)->getFont();
            $b++;
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'. 'users' .'.xls"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $excel_writer->save('php://output');
        $success = "success";
        $error = "error";

        return $this->render('admin/exportUsers.html.twig', array
        (
            'success' => $success,
            'error' => $error
        ));
    }

    public function setPaginator(Paginator $paginator)
    {
        // TODO: Implement setPaginator() method.
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
}
