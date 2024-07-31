<?php

namespace App\Controller;

use DateTime;
use App\Service\ApiService;
use App\Form\AdvancedSearchType;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MovieController extends AbstractController
{
    public function __construct(
        private ApiService $apiService,
    ) {
    }

    #[Route('/', name: 'app_movie_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->renderMoviePage('movie/index.html.twig', $request, 'https://api.themoviedb.org/3/movie/popular');
    }

    #[Route('/trending', name: 'app_movie_trending', methods: ['GET'])]
    public function trending(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->renderMoviePage('movie/index.html.twig', $request, 'https://api.themoviedb.org/3/trending/movie/day');
    }

    #[Route('show/movie/{id}', name: 'app_movie_show', methods: ['GET'])]
    public function show(string $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $movie = $this->apiService->fetchFromApi('GET', "https://api.themoviedb.org/3/movie/{$id}", ['language' => 'fr']);
        $recommendations = $this->getMovieRecommendations($id);
        $directors = $this->getDirectors($id);
        $actors = $this->getFirstActors($id);
        $favoriteMovies = $this->getUserFavoriteMovies();
        $trailer = $this->getMovieTrailer($id);

        return $this->render('movie/show.html.twig', [
            'movie' => $movie,
            'recommendations' => $recommendations,
            'directors' => $directors,
            'actors' => $actors,
            'favoriteMovies' => $favoriteMovies,
            'trailer' => $trailer,
        ]);
    }

    #[Route('search/movie', name: 'app_movie_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $searchTerm = $request->get('query');

        return $this->renderMoviePage('movie/index.html.twig', $request, 'https://api.themoviedb.org/3/search/movie', [
            'query' => $searchTerm,
        ]);
    }

    #[Route('favorites/movie', name: 'app_movie_favorites', methods: ['GET'])]
    public function fetchFavorites(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $favoriteMovies = $this->getUserFavoriteMovies();
        $movies = array_map(fn ($id) => $this->apiService->fetchFromApi('GET', "https://api.themoviedb.org/3/movie/{$id}", ['language' => 'fr']), $favoriteMovies);

        return $this->render('movie/favorites.html.twig', [
            'movies' => $movies,
            'favoriteMovies' => $favoriteMovies,
        ]);
    }

    #[Route('top-rated/movie', name: 'app_movie_top_rated', methods: ['GET'])]
    public function fetchTopRated(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->renderMoviePage('movie/index.html.twig', $request, 'https://api.themoviedb.org/3/movie/top_rated');
    }

    #[Route('casting/movie/{movieId}', name: 'app_movie_casting', methods: ['GET'])]
    public function getFullCasting(string $movieId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $actors = $this->getAllActors($movieId);
        $movie = $this->apiService->fetchFromApi('GET', "https://api.themoviedb.org/3/movie/{$movieId}", ['language' => 'fr']);

        return $this->render('movie/full_casting.html.twig', [
            'actors' => $actors,
            'movie' => $movie,
        ]);
    }

    #[Route('search/advanced', name: 'app_movie_advanced_search', methods: ['GET'])]
    public function advancedSearch(Request $request): Response
    {
        $advancedSearch = $request->getQueryString();

        return $this->renderMoviePage('movie/index.html.twig', $request, 'https://api.themoviedb.org/3/discover/movie?' . $advancedSearch);
    }

    // display advanced search form
    #[Route('search/advanced/form', name: 'app_movie_advanced_search_form', methods: ['GET'])]
    public function getAdvancedSearchForm(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $previousQueryData = [];
        parse_str($request->getQueryString(), $previousQueryData);

        $form = $this->createForm(AdvancedSearchType::class, $previousQueryData);

        return $this->render('movie/partials/_advanced_search_form.html.twig', [
            'form' => $form,
        ]);
    }

    // validate advanced search form
    #[Route('search/advanced/validate', name: 'app_movie_advanced_search_validate', methods: ['POST'])]
    public function validateAdvancedSearchData(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $form = $this->createForm(AdvancedSearchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $filteredData = array_filter($data, function($value) {
                return $value !== null && $value !== '' && $value !== [];
            });
            $formattedData = [];

            foreach ($filteredData as $key => $value) {
                $modifiedKey = $key;

                if (str_contains($key, '_lte') || str_contains($key, '_gte')) {
                    $operator = str_contains($key, '_lte') ? '.lte' : '.gte';
                    $modifiedKey = str_replace(['_lte', '_gte'], $operator, $key);

                    if (in_array($key, ['primary_release_date_lte', 'primary_release_date_gte'])) {
                        $dateString = $value . '/01/01 12:00:00';
                        $date = new DateTimeImmutable($dateString);
                        $formattedData[$modifiedKey] = $date->format('Y-m-d');
                    } else {
                        $formattedData[$modifiedKey] = $value;
                    }
                } elseif ($key === 'with_genres' || $key === 'without_genres') {
                    $formattedData[$key] = implode(',', $value);
                } else {
                    $formattedData[$key] = $value;
                }
            }
            $queryString = http_build_query($formattedData);

            return $this->json([
                'success' => true,
                'data' => $queryString,
            ]);
        } elseif ($form->isSubmitted()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }

            return $this->json([
                'success' => false,
                'errors' => $errors,
            ]);
        }
    }

    private function renderMoviePage(string $template, Request $request, string $url, array $additionalParams = []): Response
    {
        $page = $this->apiService->getPage($request);
        $advancedSearch = $request->getQueryString();
        $params = array_merge(['language' => 'fr', 'page' => $page], $additionalParams);
        $results = $this->apiService->fetchFromApi('GET', $url, $params);
        $currentPage = $results['page'];
        $totalPages = min($results['total_pages'], 500);
        $totalResults = $results['total_results'];
        $movies = $results['results'];
        $favoriteMovies = $this->getUserFavoriteMovies();

        return $this->render($template, [
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalResults' => $totalResults,
            'movies' => $movies,
            'favoriteMovies' => $favoriteMovies,
            'page' => $page,
            'advancedSearch' => $advancedSearch ?? [],
        ]);
    }

    private function getMovieRecommendations(string $id): array
    {
        $recommendations = $this->apiService->fetchFromApi('GET', "https://api.themoviedb.org/3/movie/{$id}/recommendations", ['language' => 'fr']);

        return $recommendations['results'];
    }

    private function getMovieTrailer(string $id): array
    {
        $videos = $this->apiService->fetchFromApi('GET', "https://api.themoviedb.org/3/movie/{$id}/videos", ['language' => 'fr']);
        $trailer = array_filter($videos['results'], function ($video) {
            return $video['type'] === 'Trailer' && $video['site'] === 'YouTube';
        });

        return $trailer[0] ?? [];
    }

    private function getDirectors(string $movieID): array
    {
        $credits = $this->apiService->fetchFromApi('GET', "https://api.themoviedb.org/3/movie/{$movieID}/credits", []);

        return array_filter($credits['crew'], fn ($crewMember) => $crewMember['job'] === 'Director');
    }

    private function getFirstActors(string $movieID): array
    {
        $credits = $this->apiService->fetchFromApi('GET', "https://api.themoviedb.org/3/movie/{$movieID}/credits", []);

        return array_slice($credits['cast'], 0, 5);
    }

    private function getAllActors(string $movieID): array
    {
        $credits = $this->apiService->fetchFromApi('GET', "https://api.themoviedb.org/3/movie/{$movieID}/credits", []);

        return $credits['cast'];
    }

    private function getUserFavoriteMovies(): array
    {
        $user = $this->getUser();

        return $user ? $user->getFavoriteMovies() : [];
    }
}
