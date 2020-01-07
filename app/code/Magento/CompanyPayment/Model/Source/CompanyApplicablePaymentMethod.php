<?php

namespace Magento\CompanyPayment\Model\Source;

/**
 * Class CompanyApplicablePaymentMethods.
 */
class CompanyApplicablePaymentMethod implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('B2B Payment Methods'),
            ],
            [
                'value' => 1,
                'label' => __('All Enabled Payment Methods'),
            ],
            [
                'value' => 2,
                'label' => __('Specific Payment Methods'),
            ],
        ];
    }
}
