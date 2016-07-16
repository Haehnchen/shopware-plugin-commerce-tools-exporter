<?php

namespace CommerceToolsExporter\Exporter\Image;

use CommerceToolsExporter\Client\Client;
use CommerceToolsExporter\Exception\CommerceToolsExporter;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;

class ImageExporter
{
    private $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpe' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
    ];
    
    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Client $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @param $productId
     * @param $pathname
     * @return null
     * @throws CommerceToolsExporter
     */
    public function uploadProductImage($productId, $pathname)
    {
        if(!is_file($pathname)) {
            throw new CommerceToolsExporter(sprintf('file not found "%s"', $pathname));
        }

        $ext = strtolower(pathinfo($pathname, PATHINFO_EXTENSION));
        if(!isset($this->mimeTypes[$ext])) {
            throw new \RuntimeException(sprintf('invalid file extension "%s" for image', $ext));
        }

        $options = array(
            'staged' => 'false',
            'filename' => pathinfo($pathname, PATHINFO_FILENAME) . '.' . pathinfo($pathname, PATHINFO_EXTENSION),
        );

        try {
            $this->client->upload(
                'products/' . $productId . '/images?' . http_build_query($options),
                $this->mimeTypes[$ext],
                $pathname
            );
        } catch (ClientException $e) {
            $this->logger->error(sprintf('error uploading image: %s %s', $productId, $pathname));
            return null;
        }
    }
}