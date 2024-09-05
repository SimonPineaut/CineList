<?php

namespace App\Controller;

use App\Service\ApiService;
use App\Form\AdvancedSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    public function __construct(
        private ApiService $apiService,
        #[Autowire('%tmdb_api_base_url%')] private string $tmdbApiBaseUrl
    ) {}

    #[IsGranted('ROLE_USER')]
    #[Route('search', name: 'search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $searchTerm = $request->get('query');

        return $this->renderSearchResults('movie/index.html.twig', $request, $this->tmdbApiBaseUrl . '/search/multi?vote_count.gte=100', [
            'query' => $searchTerm,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('search/advanced', name: 'movie_advanced_search', methods: ['GET'])]
    public function advancedSearch(Request $request): Response
    {
        $advancedSearch = $request->getQueryString();

        return $this->renderSearchResults('movie/index.html.twig', $request, $this->tmdbApiBaseUrl . '/discover/movie?' . $advancedSearch);
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

    private function renderSearchResults(string $template, Request $request, string $url, array $additionalParams = []): Response
    {
        $page = $this->apiService->getPage($request);
        $params = array_merge(['page' => $page], $additionalParams);
        $response = $this->apiService->fetchFromApi('GET', $url, $params);
        $currentPage = $response['page'] ?? 1;
        $totalPages = min($response['total_pages'], 500);
        $totalResults = $response['total_results'];
        $results = $response['results'];
        $favoriteMovies = $this->getUserFavoriteMovies();

        return $this->render($template, [
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalResults' => $totalResults,
            'results' => $results,
            'favoriteMovies' => $favoriteMovies,
            'page' => $page,
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
                    $date = new \DateTimeImmutable($dateString);
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

    private function getUserFavoriteMovies(): array
    {
        $user = $this->getUser();

        return $user ? $user->getFavoriteMovies() : [];
    }
}
