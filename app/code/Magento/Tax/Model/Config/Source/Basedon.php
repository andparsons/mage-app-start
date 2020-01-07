<?php
namespace Magento\Tax\Model\Config\Source;

class Basedon implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'shipping', 'label' => __('Shipping Address')],
            ['value' => 'billing', 'label' => __('Billing Address')],
            ['value' => 'origin', 'label' => __("Shipping Origin")]
        ];
    }
}
