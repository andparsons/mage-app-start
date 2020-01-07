<?php
namespace Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Address;

use Magento\AdvancedSalesRule\Model\Rule\Condition\ConcreteCondition\Address\PaymentMethod as PaymentMethodCondition;

class PaymentMethod extends AbstractFilterGenerator
{
    /**
     * @return string
     */
    protected function getFilterTextPrefix()
    {
        return PaymentMethodCondition::FILTER_TEXT_PREFIX;
    }
}
