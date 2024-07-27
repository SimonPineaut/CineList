<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PlaylistService extends AbstractController
{

    public function getPlaylistsCount(): int
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $playlistCount = count($user->getPlaylists());

        return $playlistCount;
    }
}
