<?php

namespace App\Controller;

use App\Service\ApiService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GenreController extends AbstractController
{
    public function __construct(
        private ApiService $apiService,
    ) {}

    #[Route('genres/movie', name: 'app_movie_genres', methods: ['GET'])]
    public function getGenres(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $results = $this->apiService->fetchFromApi('GET', "https://api.themoviedb.org/3/genre/movie/list", [
            'language' => 'fr',
        ]);
        $genres = $results['genres'];

        return $this->render('movie/genres.html.twig', [
            'genres' => $genres,
        ]);
    }

    #[Route('genre/movie/{genreId}/{genreName}', name: 'app_movie_genre', methods: ['GET'])]
    public function getGenre(string $genreId, string $genreName, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        $favoriteMovies = $user->getFavoriteMovies();

        $page = $this->apiService->getPage($request);

        $results = $this->apiService->fetchFromApi('GET', "https://api.themoviedb.org/3/discover/movie?with_genres={$genreId}", [
            'language' => 'fr',
            'page' => $page,
        ]);

        $page = $this->apiService->getPage($request);
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
}
