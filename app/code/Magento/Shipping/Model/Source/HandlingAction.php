<?php
namespace Magento\Shipping\Model\Source;

class HandlingAction implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Magento\Shipping\Model\Carrier\AbstractCarrier::HANDLING_ACTION_PERORDER,
                'label' => __('Per Order'),
            ],
            [
                'value' => \Magento\Shipping\Model\Carrier\AbstractCarrier::HANDLING_ACTION_PERPACKAGE,
                'label' => __('Per Package')
            ]
        ];
    }
}
