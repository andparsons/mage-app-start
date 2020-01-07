<?php

namespace Magento\CompanyCredit\Plugin\Rma\Helper;

use Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider;

/**
 * Removes Store Credit option for Returns.
 */
class EavPlugin
{
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Remove Store Credit option from Resolution field if order placed with Payment on Account method.
     *
     * @param \Magento\Rma\Helper\Eav $subject
     * @param \Closure $method
     * @param string $attributeCode
     * @param null|int|\Magento\Store\Model\Store $storeId [optional]
     * @param bool $useDefaultValue [optional]
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetAttributeOptionValues(
        \Magento\Rma\Helper\Eav $subject,
        \Closure $method,
        $attributeCode,
        $storeId = null,
        $useDefaultValue = true
    ) {
        $result = $method($attributeCode, $storeId, $useDefaultValue);
        if ($attributeCode == 'resolution') {
            $order = $this->coreRegistry->registry('current_order');
            if ($order
                && $order->getPayment()->getMethod() == CompanyCreditPaymentConfigProvider::METHOD_NAME
            ) {
                $result = array_filter(
                    $result,
                    function ($option) {
                        return !in_array($option, ['Store Credit', __('Store Credit')]);
                    }
                );
            }
        }
        return $result;
    }
}
