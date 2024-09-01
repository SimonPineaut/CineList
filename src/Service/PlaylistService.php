<?php

namespace App\Service;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PlaylistService extends AbstractController
{

    #[IsGranted('ROLE_USER')]
    public function getPlaylistsCount(): int
    {
        $user = $this->getUser();
        $playlistCount = count($user->getPlaylists());

        return $playlistCount;
    }
}
