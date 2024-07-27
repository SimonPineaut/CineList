<?php 

namespace App\Twig;

use App\Service\FavoriteService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FavoriteExtension extends AbstractExtension
{
    private $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('favorites_count', [$this, 'getFavoritesCount']),
        ];
    }

    public function getFavoritesCount(): int
    {
        return $this->favoriteService->getFavoritesCount();
    }
}
