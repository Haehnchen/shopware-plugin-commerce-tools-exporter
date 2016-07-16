<?php

namespace CommerceToolsExporter\Visitor;

use Doctrine\Common\Persistence\ObjectManager;

class ProductVisitor
{
    /**
     * ProductVisitor constructor.
     * 
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @return \Generator
     */
    public function visitArticle()
    {
        $categoryId = $this->om->getRepository('Shopware\Models\Shop\Shop')
            ->find(1) // TODO
            ->getCategory()
            ->getId();

        $qb = $this->om
            ->getRepository('Shopware\Models\Article\Article')
            ->createQueryBuilder('article');

        $qb->select('mainDetail.number');
        $qb->distinct();
        $qb->join('article.mainDetail', 'mainDetail');
        $qb->join('article.allCategories', 'allCategories');
        $qb->andWhere('allCategories.id = :mainCategory');

        $qb->setParameter('mainCategory', $categoryId);

        foreach ($qb->getQuery()->iterate() as $row) {
            yield current($row)['number'];
        }
    }
}