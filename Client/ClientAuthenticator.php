<?php

namespace CommerceToolsExporter\Client;

use CommerceToolsExporter\Config\Config;
use CommerceToolsExporter\Exception\CommerceToolsExporter;
use Doctrine\Common\Cache\Cache;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

class ClientAuthenticator
{
    const CACHE_KEY = 'AUTH_KEY';

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Config
     */
    private $config;

    public function __construct(ClientInterface $client, Cache $cache, Config $config)
    {
        $this->cache = $cache;
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * @return string "Bearer key"
     * @throws CommerceToolsExporter
     */
    public function getAuthorizationHeader()
    {
        $apiAuth = $this->getAccessToken();
        if (!isset($apiAuth['access_token'], $apiAuth['token_type'])) {
            throw new CommerceToolsExporter('api error');
        }

        return $apiAuth['token_type'] . ' ' .$apiAuth['access_token'];
    }
    
    /**
     * @return array
     * @throws CommerceToolsExporter
     */
    private function getAccessToken()
    {
        if ($this->cache->contains(self::CACHE_KEY)) {
            return $this->cache->fetch(self::CACHE_KEY);
        }

        $response = $this->requestToken();
        if(!isset($response['access_token'], $response['token_type'], $response['expires_in'])) {
            throw new CommerceToolsExporter('Invalid token response');
        }

        // cache key with expire smaller then its valid
        $this->cache->save(self::CACHE_KEY, $response, $response['expires_in'] * 0.7);
        
        return $response;
    }

    /**
     * @return array
     */
    private function requestToken()
    {
        try {
            $request = $this->client->post(Client::BASE_URL . '/oauth/token', [
                'body' => [
                    'grant_type' => 'client_credentials',
                    'scope' => 'manage_project:' . $this->config->getProject(),
                ],
                'auth' => [
                    $this->config->getClientId(),
                    $this->config->getClientSecret()
                ]
            ]);
        } catch (RequestException $e) {
            return [];
        }

        return json_decode((string) $request->getBody(), true);
    }
}