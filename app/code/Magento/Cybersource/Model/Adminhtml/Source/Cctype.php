<?php
namespace Magento\Cybersource\Model\Adminhtml\Source;

/**
 * Authorize.net Payment CC Types Source Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class Cctype extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * Return allowed cc types for current method
     *
     * @return string[]
     */
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DI', 'DN', 'JCB', 'MD', 'MI'];
    }
}
