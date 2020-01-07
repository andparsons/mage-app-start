<?php
namespace Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Address;

use Magento\AdvancedSalesRule\Model\Rule\Condition\ConcreteCondition\Address\Region as RegionCondition;

class Region extends AbstractFilterGenerator
{
    /**
     * @return string
     */
    protected function getFilterTextPrefix()
    {
        return RegionCondition::FILTER_TEXT_PREFIX;
    }
}
