<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $this->emailVerifier->sendEmailConfirmation(
                'verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('noreply@CineList.com', 'L\'équipe CineList'))
                    ->to($user->getEmail())
                    ->subject('Veuillez confirmer votre email')
                    ->htmlTemplate('account/emails/confirmation_email.html.twig')
            );

            $this->addFlash('success', 'Un email de confirmation vous a été envoyé. Veuillez vérifier votre boîte mail');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('account/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            throw $this->createNotFoundException();
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            throw $this->createNotFoundException();
        }

        if ($user->isVerified()) {
            $this->addFlash('warning', 'Votre compte est déjà validé, vous pouvez vous connecter');
            
            return $this->redirectToRoute('app_login');
        };

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle', 'fr'));

            return $this->redirectToRoute('register');
        }

        $this->addFlash('success', 'Votre adresse email a bien été validée. Connectez-vous');

        return $this->redirectToRoute('app_login');
    }

    #[Route('/verify/resend', name: 'verify_resend_email')]
    public function resendVerifyEmail(UserRepository $userRepository, Request $request)
    {
        $userId = $request->query->get('userId');

        if (null === $userId) {
            throw $this->createNotFoundException();
        }

        $user = $userRepository->find($userId);

        if (null === $user) {
            throw $this->createNotFoundException();
        };

        $this->emailVerifier->sendEmailConfirmation(
            'verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address('noreply@CineList.com', 'L\'équipe CineList'))
                ->to($user->getEmail())
                ->subject('Veuillez confirmer votre email')
                ->htmlTemplate('account/emails/confirmation_email.html.twig')
        );

        return $this->render('account/emails/resend_confirmation_email.html.twig', [
            'userId' => $userId,
        ]);
    }

    #[Route('/verify/username', name: 'verify_username', methods: 'POST')]
    public function isUserNameAvailable(UserRepository $userRepository, Request $request): JsonResponse
    {
        $usernameData = json_decode($request->getContent(), true);
        $usernameToCheck = $usernameData['usernameToCheck'];
        $existingUser = $userRepository->findOneBy(['username' => $usernameToCheck]);
        
        if ($existingUser) {
            return new JsonResponse(false);
        } else {
            return new JsonResponse(true);
        }
    }
}
