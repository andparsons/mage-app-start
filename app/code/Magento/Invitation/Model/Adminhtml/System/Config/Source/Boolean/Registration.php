<?php

/**
 * Invitation config source for customer registration field
 */
namespace Magento\Invitation\Model\Adminhtml\System\Config\Source\Boolean;

class Registration implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [1 => __('By Invitation Only'), 0 => __('Available to All')];
    }
}
