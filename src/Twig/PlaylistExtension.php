<?php 

namespace App\Twig;

use App\Service\PlaylistService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PlaylistExtension extends AbstractExtension
{
    public function __construct(private PlaylistService $playlistService)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('playlists_count', [$this, 'getPlaylistsCount']),
        ];
    }

    public function getPlaylistsCount(): int
    {
        return $this->playlistService->getPlaylistsCount();
    }
}
