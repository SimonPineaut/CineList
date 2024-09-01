<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginFlashMessageHandler implements AuthenticationSuccessHandlerInterface
{

    public function __construct(private RouterInterface $router)
    {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();
        $username = $user->getUsername();
        $request->getSession()->getFlashBag()->add('info', 'Bienvenue !');

        $targetUrl = $this->router->generate('movie_index'); 

        return new RedirectResponse($targetUrl);
    }
}