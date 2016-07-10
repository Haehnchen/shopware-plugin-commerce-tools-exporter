<?php

namespace CommerceToolsExporter\Exporter\Category;

use CommerceToolsExporter\Client\Client;
use CommerceToolsExporter\Exporter\ExportContext;
use CommerceToolsExporter\Repository\CategoryRepository;
use CommerceToolsExporter\Request\Request;
use CommerceToolsExporter\Response\ResponseUtil;
use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\Exception\BadResponseException;

class CategoryExporter
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var CategoryRepository
     */
    private $repository;

    public function __construct(ObjectManager $om, Client $client, CategoryRepository $repository)
    {
        $this->om = $om;
        $this->client = $client;
        $this->repository = $repository;
    }

    /**
     * @param ExportContext $context
     */
    public function export(ExportContext $context)
    {
        foreach ($this->all() as $category) {
            $id = (string)$category['id'];

            if($this->repository->findCategoryByExternalId($id)) {
                $context->getLogger()->notice('Update not supported ' . $category['id']);
                continue;
            }

            $request = new Request('categories', 'POST', [
                'name' => [
                    "en" => $category['name'],
                ],
                'slug' => [
                    "en" => preg_replace('#[^0-9a-z-_./]#i', '', $id . '-' . $category['name']), // @TODO: seo url
                ],
                'externalId' => $id,
            ]);

            try {
                $this->client->send($request);
                $context->getLogger()->info('OK ' . $category['id']);
            } catch (BadResponseException $e) {
                $content = json_decode((string) $e->getResponse()->getBody(), true);
                if(isset($content['message'])) {
                    $context->getLogger()->error(sprintf('[%s] error "%s"', $category['id'], ResponseUtil::formatError($content)));
                }
            }
        }
    }

    /**
     * @return \Generator
     */
    private function all()
    {
        /** @var \Shopware\Models\Shop\Shop $shop */
        $shop = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop')
            ->getDefault();

        $results = Shopware()->Models()->getRepository('Shopware\Models\Category\Category')
            ->getFullChildrenList($shop->getCategory()->getId());

        foreach($results as $result) {
            yield $result;
        }
    }
}