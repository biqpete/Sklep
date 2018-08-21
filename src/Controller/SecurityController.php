<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 04/08/2018
 * Time: 18:01
 */

namespace App\Controller;

use App\Form\UserType;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\MonologBundle\SwiftMailer;
use Symfony\Bundle\SwiftmailerBundle;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $utils)
    {
        $error = $utils->getLastAuthenticationError();
        $lastUserName = $utils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'error' => $error,
            'last_username' =>  $lastUserName
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {

    }

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, AuthenticationUtils $utils, \Swift_Mailer $mailer)
    {
        $error = $utils->getLastAuthenticationError();
        $lastUserName = $utils->getLastUsername();
        // 1) build the form
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $activationHash = md5(random_bytes(25));
            $user->setHash($activationHash);
            $user->setIsActive(false);

            $message = (new \Swift_Message("Activate your account on Peter's Shop"))
                ->setFrom('petermailer777@gmail.com')
                ->setTo($user->getEmail())
//                    ->setTo("ochenx@gmail.com")
                ->setBody("Hello ".ucfirst($user->getUsername()).'! '.
                    "Click the link below to activate your account ".
                    "http://localhost:8000/auth/".$activationHash
                )
            ;
            $mailer->send($message);

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);
            $username = $user->getUsername();
            // 4) save the User!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);

            try {
                $entityManager->flush();
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash(
                    'error',
                    'This username is already taken.'
                );

                return $this->redirectToRoute('register');
            }

            $this->addFlash(
                'notice',
                'Activation email has been sent. Check your email to activate the account'
            );

            return $this->redirectToRoute('login');
        }

        return $this->render(
            'security/register.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
            'last_username' => $lastUserName
        ]);
    }

    /**
     * @Route("/auth/{slug}", name="auth")
     */
    public function activateUser($slug, \Swift_Mailer $mailer)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(
            [
                'hash' => $slug
            ]
        );
        if(!empty($user))
        {
            $user->setIsActive(true);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $username = $user->getUsername();
            $message = (new \Swift_Message('Hello '.ucfirst($username).'!'))
                ->setFrom('petermailer777@gmail.com')
                ->setTo($user->getEmail())
//                    ->setTo("ochenx@gmail.com")
                ->setBody("Hello ".ucfirst($username).'! '.
                    "You're now registered on Peter's Shop! Use your credentials to login.")
            ;
            $mailer->send($message);

    //        dump($user);die;

            return $this->render('security/login.html.twig', [
                'registered' => 'Registered!'
            ]);
        }
        return new Response("There was an error - check your input");
    }
}
