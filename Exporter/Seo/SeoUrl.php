<?php

namespace CommerceToolsExporter\Exporter\Seo;

use CommerceToolsExporter\Exporter\ExportContext;
use Doctrine\DBAL\Connection;

class SeoUrl
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param ExportContext $context
     * @param integer $articleId
     * @return string
     */
    public function getArticleSeoUrlWithFallback(ExportContext $context, $articleId)
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select(['path'])
            ->from('s_core_rewrite_urls', 'urls')
            ->where('main = 1')
            ->andWhere('subshopID = :shopId')
            ->setParameter(':shopId', $context->getShopId());

        $qb->andWhere('org_path LIKE ' . $qb->createNamedParameter('sViewport=detail&sArticle=' . $articleId . '%'));
        $qb->setMaxResults(1);

        $statement = $qb->execute();

        // fallback
        if (!$url = $statement->fetchColumn()) {
            return 'detail/index/sArticle/' . $articleId;
        }

        return strtolower($url);
    }
}