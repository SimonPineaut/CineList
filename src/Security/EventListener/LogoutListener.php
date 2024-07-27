<?php

namespace App\Security\EventListener;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: LogoutEvent::class, method: 'onLogoutEvent')]
final class LogoutListener
{
    public function __construct(private RouterInterface $router, private UrlGeneratorInterface $urlGenerator)
    {}
    
    public function onLogoutEvent(LogoutEvent $event)
    {
        $request = $event->getRequest();
        $request->getSession()->getFlashBag()->add('success', 'Vous avez été déconnecté(e)');

        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse(['redirect' => $this->urlGenerator->generate('app_login')]);
        } else {
            $response = new RedirectResponse($this->urlGenerator->generate('app_login'));
        }

        $event->setResponse($response);
    }
}