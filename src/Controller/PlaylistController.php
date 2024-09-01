<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Playlist;
use App\Service\ApiService;
use App\Repository\UserRepository;
use App\Repository\PlaylistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/playlist')]
class PlaylistController extends AbstractController
{
    private string $tmdbApiBaseUrl;

    public function __construct(
        private PlaylistRepository $playlistRepository,
        private ApiService $apiService,
        ParameterBagInterface $params
    ) {
        $this->tmdbApiBaseUrl = $params->get('tmdb_api_base_url');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/', name: 'playlist_index', methods: ['GET'])]
    public function index(): Response
    {
        $playlists = array_filter(
            $this->playlistRepository->findAll(),
            fn(Playlist $playlist) => $playlist->isPublic()
        );

        return $this->render('playlist/index.html.twig', [
            'playlists' => $playlists,
            'moviesByPlaylist' => $this->getMoviesByPlaylist($playlists),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/logged-user-playlists', name: 'playlist_logged_user', methods: ['GET'])]
    public function getLoggedUserPlaylists(): Response
    {
        $playlists = $this->playlistRepository->findBy(['user' => $this->getUser()]);

        return $this->render('playlist/index.html.twig', [
            'playlists' => $playlists,
            'moviesByPlaylist' => $this->getMoviesByPlaylist($playlists),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/user-playlists/{userId}', name: 'playlist_user', methods: ['GET'])]
    public function getUserPlaylists(string $userId, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($userId) ?? throw new NotFoundHttpException('User not found');
        $playlists = array_filter(
            $this->playlistRepository->findBy(['user' => $userId]),
            fn(Playlist $playlist) => $playlist->isPublic()
        );

        return $this->render('playlist/index.html.twig', [
            'playlists' => $playlists,
            'moviesByPlaylist' => $this->getMoviesByPlaylist($playlists),
            'username' => $user->getUsername(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/create-playlist', name: 'playlist_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $playlistData = json_decode($request->getContent(), true);
        $playlist = (new Playlist())
            ->setName($playlistData['title'] ?? 'Untitled Playlist')
            ->addMovieId($playlistData['movieId'])
            ->setCreatedAt(new DateTimeImmutable())
            ->setUser($this->getUser())
            ->setIsPublic(false);

        $entityManager->persist($playlist);
        $entityManager->flush();

        $this->addFlash('success', "Ajouté à la playlist '{$playlist->getName()}'");

        return new JsonResponse(['success' => 'Film ajouté à la playlist créée'], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/update-playlist-status', name: 'playlist_update_status', methods: ['POST'])]
    public function updateStatus(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $playlistData = json_decode($request->getContent(), true);
        $playlist = $this->playlistRepository->find($playlistData['playlistId']) 
            ?? throw new NotFoundHttpException('Playlist not found');

        $playlist->setIsPublic(!$playlist->isPublic());
        $entityManager->persist($playlist);
        $entityManager->flush();

        $status = $playlist->isPublic() ? 'publique' : 'privée';
        $this->addFlash('info', "La playlist '{$playlist->getName()}' est désormais $status");

        return new JsonResponse(['success' => 'Status de la playlist modifié'], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/import-playlist/{id}', name: 'playlist_import', methods: ['GET'])]
    public function import(Playlist $playlist, EntityManagerInterface $entityManager): JsonResponse
    {
        $copiedPlaylist = (new Playlist())
            ->setName('Copie de ' . $playlist->getName())
            ->setCreatedAt(new DateTimeImmutable())
            ->setUser($this->getUser())
            ->setIsPublic(false);

        foreach ($playlist->getMovieIds() as $movieId) {
            $copiedPlaylist->addMovieId($movieId);
        }

        $entityManager->persist($copiedPlaylist);
        $entityManager->flush();

        $this->addFlash('success', 'Playlist copiée');

        return new JsonResponse(['success' => 'Playlist copiée'], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/add-to-playlist', name: 'playlist_add', methods: ['POST'])]
    public function addToPlaylist(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $playlistData = json_decode($request->getContent(), true);
        $playlist = $this->playlistRepository->find($playlistData['playlistId']) 
            ?? throw new NotFoundHttpException('Playlist not found');

        if (in_array($playlistData['movieId'], $playlist->getMovieIds())) {
            $this->addFlash('warning', "Le film est déjà dans la playlist '{$playlist->getName()}'");
            return new JsonResponse(['warning' => 'Déjà dans cette playlist'], Response::HTTP_OK);
        }

        $playlist->addMovieId($playlistData['movieId']);
        $playlist->setUpdatedAt(new DateTimeImmutable());

        $entityManager->persist($playlist);
        $entityManager->flush();

        $this->addFlash('success', "Ajouté à la playlist '{$playlist->getName()}'");

        return new JsonResponse(['success' => 'Ajouté à la playlist'], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/remove-from-playlist', name: 'playlist_remove_from_playlist', methods: ['POST'])]
    public function removeFromPlaylist(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $playlistData = json_decode($request->getContent(), true);
        $playlist = $this->playlistRepository->find($playlistData['playlistId']) 
            ?? throw new NotFoundHttpException('Playlist not found');

        $playlist->removeMovieId($playlistData['movieId']);

        $entityManager->persist($playlist);
        $entityManager->flush();

        $this->addFlash('success', "Retiré de la playlist '{$playlist->getName()}'");

        return new JsonResponse(['success' => 'Retiré de la playlist'], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/delete-playlist', name: 'playlist_delete', methods: ['POST'])]
    public function deletePlaylist(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $playlistData = json_decode($request->getContent(), true);
        $playlist = $this->playlistRepository->find($playlistData['playlistId']) 
            ?? throw new NotFoundHttpException('Playlist not found');

        $entityManager->remove($playlist);
        $entityManager->flush();

        $this->addFlash('warning', "Playlist '{$playlist->getName()}' supprimée");

        return new JsonResponse(['success' => 'Playlist supprimée'], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/fetch-user-playlists', name: 'playlist_get_user_playlists', methods: ['GET'])]
    public function fetchUserPlaylists(): JsonResponse
    {
        $playlists = $this->playlistRepository->findBy(['user' => $this->getUser()]);

        $data = array_map(fn(Playlist $playlist) => [
            $playlist->getId() => $playlist->getName()
        ], $playlists);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/show', name: 'playlist_show', methods: ['GET', 'POST'])]
    public function show(Playlist $playlist): Response
    {
        return $this->render('playlist/show.html.twig', [
            'playlist' => $playlist,
            'movies' => $this->getMoviesByPlaylist([$playlist])[$playlist->getId()],
        ]);
    }

    private function getMoviesByPlaylist(array $playlists): array
    {
        $moviesByPlaylist = [];
        foreach ($playlists as $playlist) {
            $moviesByPlaylist[$playlist->getId()] = array_map(
                fn($movieId) => $this->apiService->fetchFromApi(
                    'GET',
                    "{$this->tmdbApiBaseUrl}/movie/{$movieId}",
                    ['language' => 'fr']
                ),
                $playlist->getMovieIds()
            );
        }

        return $moviesByPlaylist;
    }
}
