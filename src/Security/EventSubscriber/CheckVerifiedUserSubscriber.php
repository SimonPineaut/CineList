<?php

namespace App\Security\EventSubscriber;

use App\Entity\User;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Security\AccountNotVerifiedAuthenticationException;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class CheckVerifiedUserSubscriber implements EventSubscriberInterface
{
    public function __construct(private RouterInterface $router)
    {}
    public function onCheckPassport(CheckPassportEvent $event)
    {
        $passport = $event->getPassport();

        if (!$passport instanceof Passport) {
            throw new \Exception('Type de passeport inattendu');
        }

        $user = $passport->getUser();
        if (!$user instanceof User) {
            throw new \Exception('Type d\'utilisateur inattendu');
        }

        if (!$user->isVerified()) {
            throw new AccountNotVerifiedAuthenticationException ();
        }
    }

    public function onLoginFailure(LoginFailureEvent $event)
    {
        if (!$event->getException() instanceof AccountNotVerifiedAuthenticationException) {
            return;
        }

        $userId = $event->getPassport()->getUser()->getId();
        $url = $this->router->generate('verify_resend_email', ['userId' => $userId]);

        $response = new RedirectResponse($url);
        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return [
            CheckPassportEvent::class => ['onCheckPassport', -10],
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }
}