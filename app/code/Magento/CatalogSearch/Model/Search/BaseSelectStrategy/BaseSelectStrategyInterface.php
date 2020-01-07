<?php
namespace Magento\CatalogSearch\Model\Search\BaseSelectStrategy;

use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer;

/**
 * This interface represents strategy that will be used to create base select for search request
 *
 * @deprecated 101.0.0
 * @see \Magento\ElasticSearch
 */
interface BaseSelectStrategyInterface
{
    /**
     * Creates base select query that can be populated with additional filters
     *
     * @param SelectContainer $selectContainer
     * @return SelectContainer
     * @throws \DomainException
     */
    public function createBaseSelect(SelectContainer $selectContainer);
}
