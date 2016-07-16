<?php

namespace CommerceToolsExporter\Repository;

use CommerceToolsExporter\Client\Client;
use CommerceToolsExporter\Request\Request;

class ProductRepository
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function findOneByExternalId($externalId)
    {
        $options = [
            'filter.query' => 'variants.attributes.external-id:"' . $externalId . '"',
        ];

        $array = json_decode((string) $this->client->send(new Request('product-projections/search?' . http_build_query($options)))->getBody(), true);
        if (!isset($array['results'][0]['id'])) {
            return null;
        }

        return $array['results'][0];
    }
}