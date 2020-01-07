<?php

namespace Magento\CompanyPayment\Model;

/**
 * Class Config.
 */
class Config
{
    /**
     * Scope config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Applicable payment method xml path.
     *
     * @var string
     */
    private $xmlPathApplicablePaymentMethod = 'btob/default_b2b_payment_methods/applicable_payment_methods';

    /**
     * Payment methods xml path.
     *
     * @var string
     */
    private $xmlPathAvailablePaymentMethods = 'btob/default_b2b_payment_methods/available_payment_methods';

    /**
     * Specific applicable method value.
     *
     * @var int
     */
    private $specificApplicableMethodValue = "1";

    /**
     * Constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get applicable payment method.
     *
     * @return int
     */
    public function getApplicablePaymentMethod()
    {
        return $this->scopeConfig->getValue($this->xmlPathApplicablePaymentMethod);
    }

    /**
     * Get available payment methods.
     *
     * @return string
     */
    public function getAvailablePaymentMethods()
    {
        return $this->scopeConfig->getValue($this->xmlPathAvailablePaymentMethods);
    }

    /**
     * Get specific applicable method value.
     *
     * @return int
     */
    public function isSpecificApplicableMethodApplied()
    {
        return $this->getApplicablePaymentMethod() === $this->specificApplicableMethodValue;
    }
}
