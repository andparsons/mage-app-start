<?php
namespace Magento\AdvancedRule\Model\Condition;

/**
 * Interface \Magento\AdvancedRule\Model\Condition\FilterGroupInterface
 *
 */
interface FilterGroupInterface
{
    /**
     * @return FilterInterface[]
     */
    public function getFilters();

    /**
     * @param FilterInterface[] $filters
     * @return $this
     */
    public function setFilters($filters);
}
