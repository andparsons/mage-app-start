<?php
namespace Magento\Logging\Model\ResourceModel\Grid;

class Statuses implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get options as array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            \Magento\Logging\Model\Event::RESULT_SUCCESS => __('Success'),
            \Magento\Logging\Model\Event::RESULT_FAILURE => __('Failure')
        ];
    }
}
