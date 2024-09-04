<?php

namespace App\Controller;

use App\Service\ApiService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PersonController extends AbstractController
{
    public function __construct(
        private ApiService $apiService,
        #[Autowire('%tmdb_api_base_url%')] private string $tmdbApiBaseUrl
    ) {}

    #[Route('/persons', name: 'persons')]
    public function index(Request $request): Response
    {
        return $this->renderPersonPage('person/index.html.twig', $request, $this->tmdbApiBaseUrl . '/trending/person/week');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('show/person/{id}', name: 'person_show', methods: ['GET'])]
    public function show(string $id, TranslatorInterface $translator): Response
    {
        $person = $this->apiService->fetchFromApi('GET', $this->tmdbApiBaseUrl . "/person/{$id}");
        $relatedMovies = $this->getRelatedMovies($id);
        $crewMovies = $this->apiService->fetchFromApi('GET', $this->tmdbApiBaseUrl . "/person/{$id}/movie_credits")['crew'];
        foreach ($crewMovies as $key => $value) {
            if (isset($value['job'])) {
                $crewMovies[$key]['job'] = $translator->trans($value['job']);
            }
        }

        $mergedMovies = [];
        foreach ($crewMovies as $movie) {
            $id = $movie['id'];

            if (isset($mergedMovies[$id])) {
                $mergedMovies[$id]['job'] .= ' / ' . $movie['job'];
            } else {
                $mergedMovies[$id] = $movie;
            }
        }
        usort($mergedMovies, function($a, $b) {
            return $b['vote_average'] <=> $a['vote_average'];
        });

        return $this->render('person/show.html.twig', [
            'person' => $person,
            'relatedMovies' => $relatedMovies['results'],
            'crew' => array_values($mergedMovies),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('search/person', name: 'person_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $searchTerm = $request->get('query');

        return $this->renderPersonPage('person/index.html.twig', $request, $this->tmdbApiBaseUrl . '/search/person', [
            'query' => $searchTerm,
        ]);
    }

    #[Route('/person/credits/{personId}', name: 'person_credits')]
    public function getPersonMovies(string $personId, Request $request): Response
    {
        $url = sprintf('%s/person/%d/movie_credits', $this->tmdbApiBaseUrl, $personId);
        $results = $this->apiService->fetchFromApi('GET', $url);
        $nameUrl = str_replace('/movie_credits', '', $url);
        $name = $this->apiService->fetchFromApi('GET', $nameUrl)['name'];
        $cast = $results['cast'];
        $favoriteMovies = $this->getUser()->getFavoriteMovies();

        return $this->render('person/career.html.twig', [
            'favoriteMovies' => $favoriteMovies,
            'name' => $name,
            'results' => $cast,
        ]);
    }

    private function renderPersonPage(string $template, Request $request, string $url, array $additionalParams = []): Response
    {
        $page = $this->apiService->getPage($request);
        $advancedSearch = $request->getQueryString();
        $params = array_merge(['page' => $page], $additionalParams);
        $results = $this->apiService->fetchFromApi('GET', $url, $params);
        $currentPage = $results['page'];
        $totalPages = min($results['total_pages'], 500);
        $totalResults = $results['total_results'];
        $persons = $results['results'];

        return $this->render($template, [
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalResults' => $totalResults,
            'results' => $persons,
            'page' => $page,
            'advancedSearch' => $advancedSearch ?? [],
        ]);
    }

    private function getRelatedMovies(string $id): array
    {
        $relatedMovies = $this->apiService->fetchFromApi('GET', $this->tmdbApiBaseUrl . "/discover/movie?sort_by=vote_average.desc&vote_count.gte=100&with_people={$id}");

        return $relatedMovies;
    }
}
