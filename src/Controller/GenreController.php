<?php

namespace App\Controller;

use App\Service\ApiService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GenreController extends AbstractController
{
    private string $tmdbApiBaseUrl;

    public function __construct(
        private ApiService $apiService,
        ParameterBagInterface $params
    ) {
        $this->tmdbApiBaseUrl = $params->get('tmdb_api_base_url');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('genres/movie', name: 'movie_genres', methods: ['GET'])]
    public function getGenres(): Response
    {
        $results = $this->apiService->fetchFromApi('GET', $this->tmdbApiBaseUrl . "/genre/movie/list", [
            'language' => 'fr',
        ]);
        $genres = $results['genres'];

        return $this->render('movie/genres.html.twig', [
            'genres' => $genres,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('genre/movie/{genreId}/{genreName}', name: 'movie_genre', methods: ['GET'])]
    public function getGenre(string $genreId, string $genreName, Request $request): Response
    {
        $user = $this->getUser();
        $favoriteMovies = $user->getFavoriteMovies();
        $page = $this->apiService->getPage($request);

        $results = $this->apiService->fetchFromApi('GET', $this->tmdbApiBaseUrl . "/discover/movie?with_genres={$genreId}", [
            'language' => 'fr',
            'page' => $page,
        ]);

        $currentPage = $results['page'];
        $totalPages = $results['total_pages'] > 500 ? 500 : $results['total_pages'];
        $movies = $results['results'];

        return $this->render('movie/index.html.twig', [
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'movies' => $movies,
            'favoriteMovies' => $favoriteMovies ?? [],
            'page' => $page,
            'genreId' => $genreId,
            'genreName' => $genreName,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    public function getGenreFormOptions(): Array
    {
        $results = $this->apiService->fetchFromApi('GET', $this->tmdbApiBaseUrl . "/genre/movie/list", [
            'language' => 'fr',
        ]);
        $genres = $results['genres'];
        $formattedGenres = [];
        foreach ($genres as $genre) {
            $formattedGenres[$genre['name']] = $genre['id'];
        }

        return $formattedGenres;
    }
}
