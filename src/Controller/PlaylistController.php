<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Playlist;
use App\Repository\PlaylistRepository;
use App\Repository\UserRepository;
use App\Service\ApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/playlist')]
class PlaylistController extends AbstractController
{
    public function __construct(private PlaylistRepository $playlistRepository, private ApiService $apiService)
    {
    }

    #[Route('/', name: 'app_playlist_index', methods: ['GET'])]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $playlists = $this->playlistRepository->findAll();
        $filteredPlaylists = array_filter(
            $playlists,
            function ($playlist) {
            return $playlist->isPublic();
        });
        $moviesByPlaylist = $this->getMoviesByPlaylist($filteredPlaylists);

        return $this->render('playlist/index.html.twig', [
            'playlists' => $filteredPlaylists,
            'moviesByPlaylist' => $moviesByPlaylist,
        ]);
    }

    #[Route('/logged-user-playlists', name: 'app_playlist_logged_user', methods: ['GET'])]
    public function getLoggedUserPlaylists(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $userId = $this->getUser()->getId();
        $playlists = $this->playlistRepository->findBy(['user' => $userId]);
        $moviesByPlaylist = $this->getMoviesByPlaylist($playlists);

        return $this->render('playlist/index.html.twig', [
            'playlists' => $playlists,
            'moviesByPlaylist' => $moviesByPlaylist,
        ]);
    }

    #[Route('/user-playlists/{userId}', name: 'app_playlist_user', methods: ['GET'])]
    public function getUserPlaylists(string $userId, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $playlists = $this->playlistRepository->findBy(['user' => $userId]);
        $filteredPlaylists = array_filter(
            $playlists,
            function ($playlist) {
            return $playlist->isPublic();
        });
        $moviesByPlaylist = $this->getMoviesByPlaylist($filteredPlaylists);
        $user = $userRepository->findOneBy(['id' => $userId]);
        $username = $user->getUsername();

        return $this->render('playlist/index.html.twig', [
            'playlists' => $filteredPlaylists,
            'moviesByPlaylist' => $moviesByPlaylist,
            'username' => $username,
        ]);
    }

    #[Route('/create-playlist', name: 'app_playlist_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        $playlistData = json_decode($request->getContent(), true);
        $playlistTitle = $playlistData['title'];
        $movieId = $playlistData['movieId'];
        $playlist = new Playlist();
        $playlist->setName($playlistTitle);
        $playlist->addMovieId($movieId);
        $playlist->setCreatedAt(new DateTimeImmutable());
        $playlist->setUser($user);
        $playlist->setIsPublic(false);
        
        $entityManager->persist($playlist);
        $entityManager->flush();

        $this->addFlash('success', 'Ajouté à la playlist "' . $playlistTitle . '"');

        return new JsonResponse(['success' => 'Film ajouté à la playlist créée'], 200);
    }

    #[Route('/update-playlist-status', name: 'app_playlist_update_status', methods: ['POST'])]
    public function updateStatus(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        $playlistData = json_decode($request->getContent(), true);
        $playlistId = $playlistData['playlistId'];
        $playlist = $this->playlistRepository->findOneBy(['id' => $playlistId]);
        if ($user->getId() !== $playlist->getUser()->getId()) {
            return new JsonResponse(['error' => 'Modification impossible'], 403);
        }
        
        if ($playlist->isPublic()) {
            $playlist->setIsPublic(false);
            $this->addFlash('info', 'La playlist ' . $playlist->getName() . ' est désormais privée');
        } else {
            $playlist->setIsPublic(true);
            $this->addFlash('info', 'La playlist ' . $playlist->getName() . ' est désormais publique');
        }
        
        $entityManager->persist($playlist);
        $entityManager->flush();

        return new JsonResponse(['success' => 'Status de la playlist modifié'], 200);
    }

    #[Route('/import-playlist/{id}', name: 'app_playlist_import', methods: ['GET'])]
    public function import(Playlist $playlist, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $copiedPlaylist = new Playlist;
        $copiedPlaylist->setName('copie de ' . $playlist->getName());
        $copiedPlaylist->setCreatedAt(new DateTimeImmutable());
        $copiedPlaylist->setUser($user);
        $copiedPlaylist->setIsPublic(false);
        foreach ($playlist->getMovieIds() as $movieId) {
            $copiedPlaylist->addMovieId($movieId);
        }

        $entityManager->persist($copiedPlaylist);
        $entityManager->flush();

        $this->addFlash('success', 'Playlist copiée');

        return new JsonResponse(['success' => 'Playlist copiée'], 200);
    }

    #[Route('/add-to-playlist', name: 'app_playlist_add', methods: ['POST'])]
    public function addToPlaylist(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $playlistData = json_decode($request->getContent(), true);
        $playlistId = $playlistData['playlistId'];
        $movieId = $playlistData['movieId'];
        $playlist = $this->playlistRepository->findOneBy(['id' => $playlistId]);
        $playlistName = $playlist->getName();
        $movieIds = $playlist->getMovieIds();
        if (in_array($movieId, $movieIds)) {
            $this->addFlash('warning', 'Déjà dans la playlist "' . $playlistName . '"');
    
            return new JsonResponse(['warning' => 'Déjà dans cette playlist'], 200);
        } else {
            $playlist->addMovieId($movieId);
            $playlist->setUpdatedAt(new DateTimeImmutable());
            $entityManager->persist($playlist);
            $entityManager->flush();
    
            $this->addFlash('success', 'Ajouté à la playlist' . $playlistName . '"');
    
            return new JsonResponse(['success' => 'Ajouté à la playlist'], 200);
        }
    }

    #[Route('/remove-from-playlist', name: 'app_playlist_remove_from_playlist', methods: ['POST'])]
    public function removeFromPlaylist(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $playlistData = json_decode($request->getContent(), true);
        $playlistId = $playlistData['playlistId'];
        $movieId = $playlistData['movieId'];
        $playlist = $this->playlistRepository->findOneBy(['id' => $playlistId]);
        $playlistName = $playlist->getName();

        if ($playlist) {
            $playlist = $playlist->removeMovieId($movieId);
            
            $entityManager->persist($playlist);
            $entityManager->flush();
    
            $this->addFlash('success', 'Retiré de la playlist "' . $playlistName . '"');
    
            return new JsonResponse(['success' => 'Retiré de la playlist'], 200);
        } else {
            $this->addFlash('error', 'Erreur lors du retrait de la playlist');
    
            return new JsonResponse(['error' => 'Erreur lors du retrait de la playlist'], 500);
        }
    }

    #[Route('/delete-playlist', name: 'app_playlist_delete', methods: ['POST'])]
    public function deletePlaylist(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $playlistData = json_decode($request->getContent(), true);
        $playlistId = $playlistData['playlistId'];
        $playlist = $this->playlistRepository->findOneBy(['id' => $playlistId]);
        $playlistName = $playlist->getName();

        if ($playlist) {
            $entityManager->remove($playlist);
            $entityManager->flush();
    
            $this->addFlash('warning', 'Playlist "' . $playlistName .'" supprimée');
    
            return new JsonResponse(['success' => 'Playlist supprimée'], 200);
        } else {
            $this->addFlash('error', 'Erreur lors de la suppression de la playlist');
    
            return new JsonResponse(['error' => 'Erreur lors de la suppression de la playlist'], 500);
        }
        
    }
    
    #[Route('/fetch-user-playlists', name: 'app_playlist_get_user_playlists', methods: ['GET'])]
    public function fetchUserPlaylists(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $userId = $this->getUser()->getId();
        
        $playlists = $this->playlistRepository->findBy(['user' => $userId]);
        
        $data = [];
        for ($i=0; $i < count($playlists); $i++) { 
            $data[$playlists[$i]->getId()] = $playlists[$i]->getName();
        }

        return new JsonResponse($data);
    }

    #[Route('/{id}/show', name: 'app_playlist_show', methods: ['GET', 'POST'])]
    public function show(Playlist $playlist): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $moviesByPlaylist = $this->getMoviesByPlaylist([$playlist]);

        return $this->render('playlist/show.html.twig', [
            'playlist' => $playlist,
            'movies' => $moviesByPlaylist[$playlist->getId()],
        ]);
    }
    
    private function getMoviesByPlaylist(array $playlists): array
    {
        $moviesByPlaylist = [];
        foreach ($playlists as $playlist) {
            $playlistId = $playlist->getId();
            $movieIds = $playlist->getMovieIds();

            $movies = array_map(
                fn($movieId) => $this->apiService->fetchFromApi(
                    'GET', 
                    "https://api.themoviedb.org/3/movie/{$movieId}", 
                    ['language' => 'fr']
                ),
                $movieIds
            );

            $moviesByPlaylist[$playlistId] = $movies;
        }

        return $moviesByPlaylist;
    }
}
