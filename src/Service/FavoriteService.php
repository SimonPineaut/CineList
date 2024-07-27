<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FavoriteService extends AbstractController
{

    public function getFavoritesCount(): int
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $favoritesCount = count($user->getFavoriteMovies());

        return $favoritesCount;
    }
}
