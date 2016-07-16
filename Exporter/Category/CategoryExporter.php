<?php

namespace CommerceToolsExporter\Exporter\Category;

use CommerceToolsExporter\ChangeSet\Action\Collection;
use CommerceToolsExporter\ChangeSet\ActionConverter;
use CommerceToolsExporter\ChangeSet\ChangeSet;
use CommerceToolsExporter\Client\Client;
use CommerceToolsExporter\Exporter\ExportContext;
use CommerceToolsExporter\Repository\CategoryRepository;
use CommerceToolsExporter\Request\Request;
use CommerceToolsExporter\Response\ResponseUtil;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * @var Collection
     */
    private $actions;

    /**
     * @var ActionConverter
     */
    private $actionConverter;

    public function __construct(
        ObjectManager $om,
        Client $client,
        CategoryRepository $repository,
        Collection $actions,
        ActionConverter $actionConverter
    )
    {
        $this->om = $om;
        $this->client = $client;
        $this->repository = $repository;
        $this->actions = $actions;
        $this->actionConverter = $actionConverter;
    }

    /**
     * @param ExportContext $context
     */
    public function export(ExportContext $context)
    {
        $map = $this->repository->getCategoryMap();

        foreach ($this->all() as $category) {
            $id = (string) $category['id'];

            $body = [
                'name' => [
                    'en' => $category['name'],
                ],
                'slug' => [
                    'en' => preg_replace('#[^0-9a-z-_./]#i', '', $id . '-' . $category['name']), // @TODO: seo url
                ],
                'externalId' => $id,
            ];

            if(!empty($category['metaTitle'])) {
                $body['metaTitle'] = ['en' => $category['metaTitle']];
            }

            if(!empty($category['metaDescription'])) {
                $body['metaDescription'] = ['en' => $category['metaDescription']];
            }

            if(!empty($category['metaKeywords'])) {
                $body['metaKeywords'] = ['en' => $category['metaKeywords']];
            }

            if(!empty($category['description'])) {
                $body['description'] = ['en' => $category['description']];
            }

            if(isset($map[$category['parentId']])) {
                $body['parent'] = [
                    'typeId' => 'category',
                    'id' => $map[$category['parentId']],
                ];
            }

            if($categoryExternal = $this->repository->findCategoryByExternalId($id)) {
                $this->update($context, $body, $categoryExternal);
                continue;
            }

            $request = new Request('categories', 'POST', $body);

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
     * @param ExportContext $context
     * @param array $new
     * @param array $old
     * @throws \CommerceToolsExporter\ChangeSet\Exception\ChangeSetConverterException
     */
    private function update(ExportContext $context, array $new, array $old)
    {
        $change = (new ChangeSet($old, $new, $this->actions->fields()))
            ->compute();

        $actions = $this->actionConverter->convert($change, $this->actions);
        if(count($actions) == 0) {
            $context->getLogger()->debug('No Update ' . $old['id']);
            return;
        }

        $request = new Request('categories/' . $old['id'], 'POST', [
            'version' => $old['version'],
            'actions' => $actions,
        ]);

        try {
            $this->client->send($request);
            $context->getLogger()->info('Update OK ' . $old['id']);
        } catch (BadResponseException $e) {
            $content = json_decode((string) $e->getResponse()->getBody(), true);
            if(isset($content['message'])) {
                $context->getLogger()->error(sprintf('[%s] error "%s"', $old['id'], ResponseUtil::formatError($content)));
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

        /** @var QueryBuilder $qb */
        $qb = Shopware()->Models()->getRepository('Shopware\Models\Category\Category')
            ->createQueryBuilder('categories');

        $qb->andWhere('categories.path LIKE :path');
        $qb->andWhere('categories.active = 1');
        $qb->setParameter('path', '%|' . $shop->getCategory()->getId() . '|%');

        $qb->join('categories.parent', 'parent');
        $qb->addSelect('parent');

        foreach ($qb->getQuery()->iterate(null, Query::HYDRATE_ARRAY) as $category) {
            yield $category[0];
        }
    }
}