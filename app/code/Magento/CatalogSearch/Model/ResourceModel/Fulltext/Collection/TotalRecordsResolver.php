<?php

namespace Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;

/**
 * Resolve total records count.
 *
 * For Mysql search engine we can't resolve total record count before full load of collection.
 */
class TotalRecordsResolver implements TotalRecordsResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(): ?int
    {
        return null;
    }
}
