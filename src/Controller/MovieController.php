<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Service\ApiService;
use App\Form\AdvancedSearchType;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MovieController extends AbstractController
{
    public function __construct(
        private ApiService $apiService,
        private CacheInterface $cache,
        #[Autowire('%tmdb_api_base_url%')] private string $tmdbApiBaseUrl
    ) {}

    #[Route('/', name: 'movie_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->renderMoviePage('movie/index.html.twig', $request, $this->tmdbApiBaseUrl . '/movie/popular');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/trending', name: 'movie_trending', methods: ['GET'])]
    public function trending(Request $request): Response
    {
        return $this->renderMoviePage('movie/index.html.twig', $request, $this->tmdbApiBaseUrl . '/trending/movie/day');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('show/movie/{id}', name: 'movie_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $movie = $this->getCachedMovieDetails($id);
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

    #[IsGranted('ROLE_USER')]
    #[Route('search/movie', name: 'movie_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $searchTerm = $request->get('query');

        return $this->renderMoviePage('movie/index.html.twig', $request, $this->tmdbApiBaseUrl . '/search/movie', [
            'query' => $searchTerm,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('favorites/movie', name: 'movie_favorites', methods: ['GET'])]
    public function fetchFavorites(): Response
    {
        $favoriteMovies = $this->getUserFavoriteMovies();
        $movies = array_map(fn($id) => $this->getCachedMovieDetails($id), $favoriteMovies);

        return $this->render('movie/favorites.html.twig', [
            'movies' => $movies,
            'favoriteMovies' => $favoriteMovies,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('top-rated/movie', name: 'movie_top_rated', methods: ['GET'])]
    public function fetchTopRated(Request $request): Response
    {
        return $this->renderMoviePage('movie/index.html.twig', $request, $this->tmdbApiBaseUrl . '/movie/top_rated');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('casting/movie/{movieId}', name: 'movie_casting', methods: ['GET'], requirements: ['movieId' => '\d+'])]
    public function getFullCasting(int $movieId): Response
    {
        $actors = $this->getAllActors($movieId);
        $movie = $this->getCachedMovieDetails($movieId);

        return $this->render('movie/full_casting.html.twig', [
            'actors' => $actors,
            'movie' => $movie,
        ]);
    }

    #[Route('search/advanced', name: 'movie_advanced_search', methods: ['GET'])]
    public function advancedSearch(Request $request): Response
    {
        $advancedSearch = $request->getQueryString();

        return $this->renderMoviePage('movie/index.html.twig', $request, $this->tmdbApiBaseUrl . '/discover/movie?' . $advancedSearch);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('search/advanced/form', name: 'movie_advanced_search_form', methods: ['GET'])]
    public function getAdvancedSearchForm(Request $request): Response
    {
        $queryParams = $request->query->all();

        $transform = function (&$value, $key) {
            if (in_array($key, ['primary_release_date_gte', 'primary_release_date_lte'])) {
                $value = (int) (new \DateTime($value))->format('Y');
            } elseif (in_array($key, ['vote_average_gte', 'vote_average_lte', 'vote_count_lte', 'vote_count_lte'])) {
                $value = (int) $value;
            } elseif (in_array($key, [ 'with_genres', 'without_genres'])) {
                $value = explode(",", $value);
            }
        };

        array_walk($queryParams, $transform);

        $form = $this->createForm(AdvancedSearchType::class, $queryParams);

        return $this->render('movie/partials/_advanced_search_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('search/advanced/validate', name: 'movie_advanced_search_validate', methods: ['POST'])]
    public function validateAdvancedSearchData(Request $request): Response
    {
        $form = $this->createForm(AdvancedSearchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $filteredData = array_filter($data, function ($value) {
                return $value !== null && $value !== '' && $value !== [];
            });
            $formattedData = $this->formatAdvancedSearchData($filteredData);
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
            'genreId' => [],
            'genreName' =>  [],
        ]);
    }

    private function formatAdvancedSearchData(array $data): array
    {
        $formattedData = [];

        foreach ($data as $key => $value) {
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

        return $formattedData;
    }

    private function getMovieRecommendations(string $id): array
    {
        return $this->cache->get("movie_{$id}_recommendations", function () use ($id) {
            $recommendations = $this->apiService->fetchFromApi('GET', $this->tmdbApiBaseUrl . "/movie/{$id}/recommendations", ['language' => 'fr']);
            return $recommendations['results'];
        });
    }

    private function getMovieTrailer(string $id): array
    {
        return $this->cache->get("movie_{$id}_trailer", function () use ($id) {
            $videos = $this->apiService->fetchFromApi('GET', $this->tmdbApiBaseUrl . "/movie/{$id}/videos", ['language' => 'fr']);
            $trailer = array_filter($videos['results'], function ($video) {
                return $video['type'] === 'Trailer' && $video['site'] === 'YouTube';
            });

            return $trailer[0] ?? [];
        });
    }

    private function getDirectors(string $movieID): array
    {
        return $this->cache->get("movie_{$movieID}_directors", function () use ($movieID) {
            $credits = $this->apiService->fetchFromApi('GET', $this->tmdbApiBaseUrl . "/movie/{$movieID}/credits", []);
            return array_filter($credits['crew'], fn($crewMember) => $crewMember['job'] === 'Director');
        });
    }

    private function getFirstActors(string $movieID): array
    {
        return $this->cache->get("movie_{$movieID}_actors", function () use ($movieID) {
            $credits = $this->apiService->fetchFromApi('GET', $this->tmdbApiBaseUrl . "/movie/{$movieID}/credits", []);
            return array_slice($credits['cast'], 0, 5);
        });
    }

    private function getAllActors(string $movieID): array
    {
        return $this->cache->get("movie_{$movieID}_all_actors", function () use ($movieID) {
            $credits = $this->apiService->fetchFromApi('GET', $this->tmdbApiBaseUrl . "/movie/{$movieID}/credits", []);
            return $credits['cast'];
        });
    }

    private function getUserFavoriteMovies(): array
    {
        $user = $this->getUser();

        return $user ? $user->getFavoriteMovies() : [];
    }

    private function getCachedMovieDetails(int $id): array
    {
        return $this->cache->get("movie_{$id}_details", function () use ($id) {
            return $this->apiService->fetchFromApi('GET', $this->tmdbApiBaseUrl . "/movie/{$id}", ['language' => 'fr']);
        });
    }
}
