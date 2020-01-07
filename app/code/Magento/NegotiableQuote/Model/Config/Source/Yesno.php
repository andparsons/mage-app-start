<?php

namespace Magento\NegotiableQuote\Model\Config\Source;

class Yesno implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {inheritdoc}
     */
    public function toOptionArray()
    {
        return [['value' => true, 'label' => __('Yes')], ['value' => false, 'label' => __('No')]];
    }
}
