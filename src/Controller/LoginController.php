<?php


namespace App\Controller;


use App\Entity\User;
use App\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController {

    /**
     * @Route("/register", name="app_register")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param AuthorizationCheckerInterface $aci
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, AuthorizationCheckerInterface $aci) {
        if ($aci->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('post_list');
        }

        $user = new User();

        $registerForm = $this->createForm(UserType::class, $user);
        $registerForm->handleRequest($request);

        if ($registerForm->isSubmitted() && $registerForm->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setDateRegistered(new \DateTime("now"));
            $user->setIsActive(true);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_login');
        }


        return $this->render('login/register.html.twig', [
            'form' => $registerForm->createView(),
        ]);
    }

    /**
     * @Route("/login", name="app_login")
     *
     * @param AuthenticationUtils $authUtils
     * @param AuthorizationCheckerInterface $aci
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(AuthenticationUtils $authUtils, AuthorizationCheckerInterface $aci) {
        if ($aci->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('post_list');
        }

        $error = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('login/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route(path="phpinfo", name="phpinfo")
     */
    public function phpinfo() {
        phpinfo();
    }

}