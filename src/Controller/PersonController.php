<?php

namespace App\Controller;

use App\Service\ApiService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PersonController extends AbstractController
{
    public function __construct(
        private ApiService $apiService,
    ) {}

    #[Route('show/person/{id}', name: 'app_person_show', methods: ['GET'])]
    public function show(string $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $person = $this->apiService->fetchFromApi('GET', "https://api.themoviedb.org/3/person/{$id}", [
            'language' => 'fr',
        ]);
        $relatedMovies = $this->getRelatedMovies($id);

        return $this->render('person/show.html.twig', [
            'person' => $person,
            'relatedMovies' => $relatedMovies['results'],
        ]);
    }

    public function getRelatedMovies(string $id): array
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $relatedMovies = $this->apiService->fetchFromApi('GET', "https://api.themoviedb.org/3/discover/movie?sort_by=vote_average.desc&with_people={$id}", [
            'language' => 'fr',
        ]);

        return $relatedMovies;
    }
}
