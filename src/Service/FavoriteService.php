<?php

namespace App\Service;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FavoriteService extends AbstractController
{

    #[IsGranted('ROLE_USER')]
    public function getFavoritesCount(): int
    {
        $user = $this->getUser();
        $favoritesCount = count($user->getFavoriteMovies());

        return $favoritesCount;
    }
}
