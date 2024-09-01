<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiService extends AbstractController
{
    private string $apiKey;
    private string $tmdbApiBaseUrl;

    public function __construct(
        private CacheInterface $cache,
        private HttpClientInterface $client,
        #[Autowire('%tmdb_api_base_url%')] string $tmdbApiBaseUrl,
        #[Autowire('%api_key%')] string $apiKey
    ) {
        $this->apiKey = $apiKey;
        $this->tmdbApiBaseUrl = $tmdbApiBaseUrl;
    }

    public function isValidateApiKey(): bool
    {
        try {
            $response = $this->client->request('GET', $this->tmdbApiBaseUrl . '/authentication', [
                'query' => ['api_key' => $this->apiKey],
                'headers' => ['accept' => 'application/json'],
            ]);

            return $response->getStatusCode() === Response::HTTP_OK;
        } catch (\Exception $e) {
            // Log the error for monitoring purposes
            $this->logApiError($e, 'API key validation failed');
            return false;
        }
    }

    public function fetchFromApi(string $method, string $endpoint, array $params = []): array
    {
        $cacheKey = $this->generateCacheKey($method, $endpoint, $params);

        return $this->cache->get($cacheKey, function () use ($method, $endpoint, $params) {
            try {
                $response = $this->client->request($method, $endpoint, [
                    'query' => array_merge($params, ['api_key' => $this->apiKey]),
                ]);

                if ($response->getStatusCode() !== Response::HTTP_OK) {
                    throw new \Exception('Erreur de requête API: ' . $response->getStatusCode());
                }

                return $response->toArray();
            } catch (\Exception $e) {
                $this->logApiError($e, "Error fetching data from API: {$endpoint}");
                throw new \Exception('Erreur lors de la requête API: ' . $e->getMessage());
            }
        });
    }

    public function getPage(Request $request): int
    {
        $page = $request->get('page', 1);

        if (!is_numeric($page) || (int)$page < 1) {
            throw $this->createNotFoundException('Ressource non trouvée, vérifiez l\'URL');
        }

        return (int)$page;
    }

    private function generateCacheKey(string $method, string $endpoint, array $params): string
    {
        return md5($method . $endpoint . json_encode($params));
    }

    private function logApiError(\Exception $e, string $context): void
    {
        $this->get('logger')->error("{$context}: " . $e->getMessage());
    }
}
