<?php
namespace Magento\Solr\SearchAdapter\Filter;

use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

/**
 * Interface \Magento\Solr\SearchAdapter\Filter\BuilderInterface
 *
 */
interface BuilderInterface
{
    /**
     * @param RequestFilterInterface $filter
     * @param string $conditionType
     * @return string
     */
    public function build(RequestFilterInterface $filter, $conditionType);
}
