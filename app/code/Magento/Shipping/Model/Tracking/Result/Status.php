<?php
namespace Magento\Shipping\Model\Tracking\Result;

/**
 * Fields:
 * - carrier: carrier code
 * - carrierTitle: carrier title
 */
class Status extends \Magento\Shipping\Model\Tracking\Result\AbstractResult
{
    /**
     * @return array
     */
    public function getAllData()
    {
        return $this->_data;
    }
}
