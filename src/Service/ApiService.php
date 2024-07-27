<?php

namespace App\Service;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ApiService extends AbstractController
{
    private string $apiKey;

    public function __construct(ParameterBagInterface $params,private HttpClientInterface $client)
    {
        $this->apiKey = $params->get('api_key');
    }

    public function isValidateApiKey(): bool
    {
        $response = $this->client->request('GET', 'https://api.themoviedb.org/3/authentication', [
            'headers' => [
                'accept' => 'application/json',
            ],
            'query' => [
                'api_key' => $this->apiKey,
            ],
        ]);

        return 200 === $response->getStatusCode() ? true : false;
    }

    public function fetchFromApi(string $method, string $endpoint, array $params): array
    {
        try {
            $response = $this->client->request($method, $endpoint, [
                'query' => array_merge($params, ['api_key' => $this->apiKey])
            ]);
        
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                throw new \Exception('Erreur de requête API: ' . $response->getStatusCode());
            }
            
            return $response->toArray();
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la requête API: ' . $e->getMessage());
        }
    }
    
    public function getPage(Request $request): int
    {
        $page = $request->get('page', 1);
        
        if (!is_numeric($page) || (int)$page < 1) {
            throw $this->createNotFoundException('Ressource non trouvée, vérifiez l\'URL');
        }
    
        return (int)$page;
    }
}
