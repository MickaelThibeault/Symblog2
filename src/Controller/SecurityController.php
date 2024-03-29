<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{


    #[Route('/connexion', name: 'security.login')]
    public function login(AuthenticationUtils $utils): Response
    {
        // get the login error if there is one
        $error = $utils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $utils->getLastUsername();


        return $this->render('pages/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error
        ]);
    }

    #[Route('/deconnexion', name: 'security.logout')]
    public function logout(): void
    {

    }
}