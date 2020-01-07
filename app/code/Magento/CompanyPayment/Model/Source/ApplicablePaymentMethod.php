<?php

namespace Magento\CompanyPayment\Model\Source;

/**
 * Class ApplicablePaymentMethods.
 */
class ApplicablePaymentMethod implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('All Payment Methods'),
            ],
            [
                'value' => 1,
                'label' => __('Specific Payment Methods'),
            ],
        ];
    }
}
