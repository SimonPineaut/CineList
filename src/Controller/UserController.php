<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ModifyPasswordFormType;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    #[IsGranted('ROLE_USER')]
    #[Route('/account', name: 'account')]
    public function changePassword(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ModifyPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
                $form->get('currentPassword')->addError(new FormError('Ancien mot de passe incorrect.'));
            } elseif ($newPassword !== $confirmPassword) {
                $form->get('confirmPassword')->addError(new FormError('Les deux mots de passe ne correspondent pas.'));
            } else {
                $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
                $this->entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a bien été modifié !');

                return $this->redirectToRoute('account');
            }
        }

        return $this->render('account/account.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/account/old-password', name: 'old_password')]
    public function iscurrentPasswordValid(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $currentPassword = $data['currentPassword'];

        if ($this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            return new JsonResponse(true);
        } else {
            return new JsonResponse(false);
        }
    }

    #[Route('account/delete/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(User $user, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $loggedUser = $this->getUser();

        if ($loggedUser->getId() === $user->getId()) {
            $session = $request->getSession();
            $session = new Session();
            $session->invalidate();

            $entityManager->remove($user);
            $entityManager->flush();

            $session->getFlashBag()->add('success', 'Votre compte a été supprimé avec succès');

            return new JsonResponse(true);
        } else {
            $request->getSession()->getFlashBag()->add('error', 'Erreur lors de la suppression de votre compte');
            
            return new JsonResponse(false);
        }
    }
}
