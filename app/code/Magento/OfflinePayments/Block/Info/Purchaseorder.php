<?php
namespace Magento\OfflinePayments\Block\Info;

class Purchaseorder extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_template = 'Magento_OfflinePayments::info/purchaseorder.phtml';

    /**
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('Magento_OfflinePayments::info/pdf/purchaseorder.phtml');
        return $this->toHtml();
    }
}
