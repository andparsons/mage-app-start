<?php
namespace Magento\Eway\Model\Adminhtml\Source;

use Magento\Payment\Model\Source\Cctype as PaymentCctype;

/**
 * Class Cctype provides source for backend cctypes selector
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class Cctype extends PaymentCctype
{
    /**
     * @inheritdoc
     */
    public function getAllowedTypes()
    {
        return ['AE', 'VI', 'MC', 'JCB', 'DN'];
    }

    /**
     * Geting credit cards types
     *
     * @return array
     */
    public function getCcTypes()
    {
        return $this->_paymentConfig->getCcTypes();
    }
}
