<?php
namespace Magento\AdvancedSalesRule\Model\Rule\Condition\ConcreteCondition\Address;

use Magento\AdvancedSalesRule\Model\Rule\Condition\ConcreteCondition\AbstractFilterableCondition;

class RegionId extends AbstractFilterableCondition
{
    const FILTER_TEXT_GENERATOR_CLASS =
        \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Address\RegionId::class;

    /**
     * @return string
     */
    protected function getFilterTextPrefix()
    {
        return self::FILTER_TEXT_PREFIX;
    }

    /**
     * @return string
     */
    protected function getFilterTextGeneratorClass()
    {
        return self::FILTER_TEXT_GENERATOR_CLASS;
    }
}
