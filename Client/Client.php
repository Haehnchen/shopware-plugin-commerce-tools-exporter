<?php

namespace CommerceToolsExporter\Client;

use CommerceToolsExporter\Config\Config;
use CommerceToolsExporter\Request\Request;
use GuzzleHttp\ClientInterface;

class Client
{
    const BASE_URL = 'https://api.sphere.io';

    /**
     * @var ClientAuthenticator
     */
    private $authenticator;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Config
     */
    private $config;

    public function __construct(ClientAuthenticator $authenticator, Config $config, ClientInterface $client)
    {
        $this->authenticator = $authenticator;
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * @param Request $request
     * @return \GuzzleHttp\Message\ResponseInterface
     * @throws \CommerceToolsExporter\Exception\CommerceToolsExporter
     */
    public function send(Request $request)
    {
        $headers = [
            'Authorization' => $this->authenticator->getAuthorizationHeader(),
        ];

        if(strtolower($request->getMethod()) == 'post') {
            return $this->client->post(self::BASE_URL . '/' . $this->config->getProject() . '/' . $request->getEndpoint(), [
                'body' => json_encode($request->getBody()),
                'headers' => $headers
            ]);
        }

        return $this->client->get(self::BASE_URL . '/' . $this->config->getProject() . '/' . $request->getEndpoint(), [
            'headers' => $headers
        ]);
    }
    
    public function upload($url, $mimeType, $filename)
    {
        $headers = [
            'Authorization' => $this->authenticator->getAuthorizationHeader(),
            'Content-Type' => $mimeType,
        ];

        return $this->client->post(self::BASE_URL . '/' . $this->config->getProject() . '/' . $url, [
            'headers' => $headers,
            'body' => fopen($filename, 'r'),
        ]);
    }
}