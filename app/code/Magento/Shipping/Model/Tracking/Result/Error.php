<?php
namespace Magento\Shipping\Model\Tracking\Result;

class Error extends \Magento\Shipping\Model\Tracking\Result\AbstractResult
{
    /**
     * @return array
     */
    public function getAllData()
    {
        return $this->_data;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getErrorMessage()
    {
        return __('Tracking information is unavailable.');
    }
}
