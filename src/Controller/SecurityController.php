<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils, Request $request)
    {
        $referer = $request->headers->get('referer');
        $anchor = $request->get('anchor');
        $refererUrl = null;

        if ((!strpos($referer, "user/add")) && (!strpos($referer, "resetting"))){
            $refererUrl = $referer . "#" . $anchor;
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'current_menu' => 'login',
            'referer' => $refererUrl
        ]);
    }
}