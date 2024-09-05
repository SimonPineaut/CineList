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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PersonController extends AbstractController
{
    public function __construct(
        private ApiService $apiService,
        #[Autowire('%tmdb_api_base_url%')] private string $tmdbApiBaseUrl
    ) {}

    #[IsGranted('ROLE_USER')]
    #[Route('/persons', name: 'persons', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->renderPersonPage('person/index.html.twig', $request, "{$this->tmdbApiBaseUrl}/trending/person/week");
    }

    #[IsGranted('ROLE_USER')]
    #[Route('show/person/{id}', name: 'person_show', methods: ['GET'])]
    public function show(string $id, TranslatorInterface $translator): Response
    {
        $person = $this->apiService->fetchFromApi('GET', "{$this->tmdbApiBaseUrl}/person/{$id}");
        if (!$person) {
            throw new NotFoundHttpException("Personne non trouvée.");
        }

        $relatedMovies = $this->getRelatedMovies($id);
        $crewMovies = $this->translateCrewJobs($this->getPersonCrewMovies($id), $translator);
        $mergedMovies = $this->mergeCrewMovies($crewMovies);

        return $this->render('person/show.html.twig', [
            'person' => $person,
            'relatedMovies' => $relatedMovies['results'],
            'crew' => $mergedMovies,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/person/credits/{personId}', name: 'person_credits', methods: ['GET'])]
    public function getPersonMovies(string $personId, TranslatorInterface $translator): Response
    {
        $url = sprintf('%s/person/%s/movie_credits', $this->tmdbApiBaseUrl, $personId);
        $nameUrl = str_replace('/movie_credits', '', $url);
        $name = $this->apiService->fetchFromApi('GET', $nameUrl)['name'];
        $crewMovies = $this->translateCrewJobs($this->getPersonCrewMovies($personId), $translator);
        $moviesAsCrew = $this->mergeCrewMovies($crewMovies);
        $moviesAsCast = $this->getPersonCastMovies($personId);

        $movies = [];
        $ids = [];
        foreach (array_merge($moviesAsCast, $moviesAsCrew) as $item) {
            if (!in_array($item['id'], $ids)) {
                $ids[] = $item['id'];
                $movies[] = $item;
            }
        };
        $favoriteMovies = $this->getUser()->getFavoriteMovies();

        return $this->render('person/career.html.twig', [
            'favoriteMovies' => $favoriteMovies,
            'name' => $name,
            'personId' => $personId,
            'results' => $movies,
        ]);
    }

    private function renderPersonPage(string $template, Request $request, string $url, array $additionalParams = []): Response
    {
        $page = $this->apiService->getPage($request);
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
            'advancedSearch' => $request->getQueryString() ?? [],
        ]);
    }

    private function getRelatedMovies(string $id): array
    {
        return $this->apiService->fetchFromApi('GET', "{$this->tmdbApiBaseUrl}/discover/movie?sort_by=vote_average.desc&vote_count.gte=100&with_people={$id}");
    }

    private function getPersonCrewMovies(string $id): array
    {
        return $this->apiService->fetchFromApi('GET', "{$this->tmdbApiBaseUrl}/person/{$id}/movie_credits")['crew'];
    }

    private function getPersonCastMovies(string $id): array
    {
        return $this->apiService->fetchFromApi('GET', "{$this->tmdbApiBaseUrl}/person/{$id}/movie_credits")['cast'];
    }

    private function translateCrewJobs(array $crewMovies, TranslatorInterface $translator): array
    {
        foreach ($crewMovies as $key => $movie) {
            if (isset($movie['job'])) {
                $crewMovies[$key]['job'] = $translator->trans($movie['job']);
            }
        }
        return $crewMovies;
    }

    private function mergeCrewMovies(array $crewMovies): array
    {
        $mergedMovies = [];
        foreach ($crewMovies as $movie) {
            $id = $movie['id'];

            if (isset($mergedMovies[$id])) {
                $mergedMovies[$id]['job'] .= ' / ' . $movie['job'];
            } else {
                $mergedMovies[$id] = $movie;
            }
        }
        usort($mergedMovies, fn($a, $b) => $b['vote_average'] <=> $a['vote_average']);

        return array_values($mergedMovies);
    }
}

