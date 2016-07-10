<?php

namespace CommerceToolsExporter\Repository;

use CommerceToolsExporter\Client\Client;
use CommerceToolsExporter\Request\Request;

class CategoryRepository
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $externalId
     * @return null|array
     */
    public function findCategoryByExternalId($externalId)
    {
        $options = [
            'where' => http_build_query([
                'externalId' => $externalId,
            ]),
            'limit' => 1,
        ];

        $response = $this->client->send(new Request('categories?' . http_build_query($options)));
        if ($response->getStatusCode() != 200) {
            return null;
        }

        $array = json_decode((string) $response->getBody(), true);
        if (!isset($array['total']) || $array['total'] === 0) {
            return null;
        }

        return $array['results'][0];
    }    
}