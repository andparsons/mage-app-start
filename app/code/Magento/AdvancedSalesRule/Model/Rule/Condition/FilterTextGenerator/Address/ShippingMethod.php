<?php
namespace Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Address;

use Magento\AdvancedSalesRule\Model\Rule\Condition\ConcreteCondition\Address\ShippingMethod as ShippingMethodCondition;

class ShippingMethod extends AbstractFilterGenerator
{
    /**
     * @return string
     */
    protected function getFilterTextPrefix()
    {
        return ShippingMethodCondition::FILTER_TEXT_PREFIX;
    }
}
