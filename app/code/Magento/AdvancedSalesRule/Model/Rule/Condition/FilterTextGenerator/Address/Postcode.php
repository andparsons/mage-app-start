<?php
namespace Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Address;

use Magento\AdvancedSalesRule\Model\Rule\Condition\ConcreteCondition\Address\Postcode as PostcodeCondition;

class Postcode extends AbstractFilterGenerator
{
    /**
     * @return string
     */
    protected function getFilterTextPrefix()
    {
        return PostcodeCondition::FILTER_TEXT_PREFIX;
    }
}
