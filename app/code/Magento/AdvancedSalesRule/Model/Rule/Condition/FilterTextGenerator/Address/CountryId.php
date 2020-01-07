<?php
namespace Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Address;

use Magento\AdvancedSalesRule\Model\Rule\Condition\ConcreteCondition\Address\CountryId as CountryIdCondition;

class CountryId extends AbstractFilterGenerator
{
    /**
     * @return string
     */
    protected function getFilterTextPrefix()
    {
        return CountryIdCondition::FILTER_TEXT_PREFIX;
    }
}
