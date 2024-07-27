<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FavoriteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('favorites/add/movie/{movieID}', name: 'app_favorite_add', methods: ['GET'])]
    public function addToFavoriteMovies(string $movieID): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $user->addToFavorites($movieID);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->addFlash('success', 'Ajouté aux favoris');

        return new JsonResponse(['success' => 'Ajouté aux favoris'], 200);
    }

    #[Route('favorites/remove/movie/{movieID}', name: 'app_favorite_remove', methods: ['GET'])]
    public function removeFromFavoriteMovies(string $movieID): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $user->removeFromFavorites($movieID);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->addFlash('error', 'Favori supprimé');

        return new JsonResponse(['success' => 'Favori supprimé'], 200);
    }
}
