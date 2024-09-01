<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FetchFlashMessagesController extends AbstractController
{
    #[Route('/ajax-flashes', name: 'fetch_flash_messages')]
    public function index(Request $request): JsonResponse
    {
        $flashMessages = $request->getSession()->getFlashBag()->all();

        foreach ($flashMessages as $key => $value) {
            if (count($value) > 1) {
                foreach ($value as $message) {
                    return new JsonResponse([$key => $message]);
                }
            }

            return new JsonResponse($flashMessages);
        }

        return new JsonResponse(null);
    }
}
