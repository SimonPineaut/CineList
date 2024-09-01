<?php

namespace App\Controller;

use App\Service\ApiService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PersonController extends AbstractController
{
    private string $tmdbApiBaseUrl;

    public function __construct(
        private ApiService $apiService,
        ParameterBagInterface $params,
    ) {
        $this->tmdbApiBaseUrl = $params->get('tmdb_api_base_url');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('show/person/{id}', name: 'person_show', methods: ['GET'])]
    public function show(string $id): Response
    {        
        $person = $this->apiService->fetchFromApi('GET', $this->tmdbApiBaseUrl. "/person/{$id}", [
            'language' => 'fr',
        ]);
        $relatedMovies = $this->getRelatedMovies($id);

        return $this->render('person/show.html.twig', [
            'person' => $person,
            'relatedMovies' => $relatedMovies['results'],
        ]);
    }

    #[IsGranted('ROLE_USER')]
    public function getRelatedMovies(string $id): array
    {        
        $relatedMovies = $this->apiService->fetchFromApi('GET', $this->tmdbApiBaseUrl . "/discover/movie?sort_by=vote_average.desc&vote_count.gte=100&with_people={$id}", [
            'language' => 'fr',
        ]);

        return $relatedMovies;
    }
}
