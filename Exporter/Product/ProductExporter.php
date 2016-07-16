<?php

namespace CommerceToolsExporter\Exporter\Product;

use CommerceToolsExporter\Client\Client;
use CommerceToolsExporter\Config\Config;
use CommerceToolsExporter\Exception\CommerceToolsExporter;
use CommerceToolsExporter\Exporter\ExportContext;
use CommerceToolsExporter\Exporter\Image\ImageExporter;
use CommerceToolsExporter\Exporter\Seo\SeoUrl;
use CommerceToolsExporter\Repository\CategoryRepository;
use CommerceToolsExporter\Repository\ProductRepository;
use CommerceToolsExporter\Request\Request;
use CommerceToolsExporter\Response\ResponseUtil;
use CommerceToolsExporter\Visitor\ProductVisitor;
use GuzzleHttp\Exception\BadResponseException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;

class ProductExporter
{
    /**
     * @var ProductVisitor
     */
    private $visitor;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var CategoryRepository
     */
    private $repository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var ImageExporter
     */
    private $imageExporter;
    /**
     * @var SeoUrl
     */
    private $seoUrl;

    public function __construct(
        ProductVisitor $visitor,
        Config $config,
        Client $client,
        CategoryRepository $repository,
        ProductRepository $productRepository,
        MediaServiceInterface $mediaService,
        ImageExporter $imageExporter,
        SeoUrl $seoUrl
    )
    {
        $this->visitor = $visitor;
        $this->config = $config;
        $this->client = $client;
        $this->repository = $repository;
        $this->productRepository = $productRepository;
        $this->mediaService = $mediaService;
        $this->imageExporter = $imageExporter;
        $this->seoUrl = $seoUrl;
    }

    public function export(ExportContext $context)
    {
        if(!$this->config->getProductTypeId()) {
            throw new CommerceToolsExporter('No product type id given');
        }

        $response = json_decode($this->client->send(new Request('tax-categories'))->getBody(), true);
        if(!isset($response['results'][0]['id'])) {
            throw new CommerceToolsExporter('Non default tax id');
        }

        $taxId = $response['results'][0]['id'];

        $categories = $this->repository->getCategoryMap();

        foreach($this->visitor->visitArticle() as $orderNumber) {
            if(null === $article = $this->getArticle($context, $orderNumber)) {
                continue;
            }

            if(null === $product = $this->mapProduct($context, $article, $taxId, $categories)) {
                continue;
            }

            $id = $article['mainDetail']['number'];

            if($external = $this->productRepository->findOneByExternalId($id)) {
                $context->getLogger()->info('update not supported');
                if(isset($article['images'])) {
                    $this->uploadImages($context, $external, $article['images']);
                }

                continue;
            }

            $request = new Request('products', 'POST', $product);

            try {
                $this->client->send($request);
                $context->getLogger()->info('OK ' . $id);
            } catch (BadResponseException $e) {
                $content = json_decode((string) $e->getResponse()->getBody(), true);
                if(isset($content['message'])) {
                    $context->getLogger()->error(sprintf('[%s] error "%s"', $id, ResponseUtil::formatError($content)));
                }
            }
        }
    }

    private function mapProduct(ExportContext $context, array $swProduct, $taxId, array $categoryMap)
    {
        $seoUrl = $this->seoUrl->getArticleSeoUrlWithFallback($context, $swProduct['id']);

        $product = [
            'name' => [
                'en' => $swProduct['name'],
            ],
            'slug' => [
                'en' => preg_replace('#[^0-9a-z-_]#i', '-', $seoUrl), // @TODO: seo url,
            ],
            'productType' => [
                'id' => $this->config->getProductTypeId(),
                'typeId' => 'product-type',
            ],
            'masterVariant' => [
                'prices' => [
                    [
                        'value' => [
                            'currencyCode' => 'EUR',
                            'centAmount' => round($swProduct['mainDetail']['prices'][0]['price'] * 119), // @TODO
                        ]
                    ]
                ],
                'attributes' => [
                    [
                        'name' => 'external-id',
                        'value' => $swProduct['mainDetail']['number'],
                    ],
                    [
                        'name' => 'seo-url',
                        'value' => $seoUrl,
                    ],
                ],
            ],
            'taxCategory' => [
                'id' => $taxId,
                'typeId' => 'tax-category',
            ],
            'publish' => true,
        ];

        if(isset($swProduct['descriptionLong'])) {
            $product['description']['en'] = $swProduct['descriptionLong'];
        }

        if(isset($swProduct['categories'])) {
            foreach($swProduct['categories'] as $category) {
                if(!isset($category['id'])) {
                    continue;
                }

                if(!isset($categoryMap[$category['id']])) {
                    continue;
                }

                $product['categories'][] = [
                    'typeId' =>'product',
                    'id' => $categoryMap[$category['id']],
                ];
            }
        }

        if (count($swProduct['details']) > 0) {
            foreach($swProduct['details'] as $variant) {
                $product['variants'][] = [
                    'sku' => $variant['number'],
                    'price' => round($variant['prices'][0]['price'] * 119), // @TODO
                ];
            }
        }

        return $product;
    }

    /**
     * @param ExportContext $context
     * @param $orderNumber
     * @return array|\Shopware\Models\Article\Article
     */
    private function getArticle(ExportContext $context, $orderNumber)
    {
        try {
            return \Shopware\Components\Api\Manager::getResource('Article')
                ->getOneByNumber($orderNumber);
        } catch (NotFoundException $e) {
            $context->getLogger()->info('Article not found: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * @param ExportContext $context
     * @param array $externalProduct
     * @param array $images
     * @throws CommerceToolsExporter
     */
    private function uploadImages(ExportContext $context, array $externalProduct, array $images)
    {
        if (isset($externalProduct['masterVariant']['images']) && count($externalProduct['masterVariant']['images']) > 0) {
            $context->getLogger()->debug('Skipping image upload');
            return;
        }

        foreach ($images as $image) {
            $path = $this->mediaService
                ->encode('media/image/' . $image['path'] . '.' . $image['extension']);

            $this->imageExporter->uploadProductImage($externalProduct['id'], $path);
            $context->getLogger()->info('Image uploaded: ' . $path);
        }
    }
}