<?php

namespace Magento\CompanyPayment\Model\Payment;

/**
 * Class AvailabilityChecker.
 */
class AvailabilityChecker
{
    /**
     * Use b2b settings.
     *
     * @var string
     */
    private $useBtob = "0";

    /**
     * Use all.
     *
     * @var string
     */
    private $useAll = "1";

    /**
     * Use specific.
     *
     * @var string
     */
    private $useSpecific = "2";

    /**
     * @var \Magento\CompanyPayment\Model\Config
     */
    private $config;

    /**
     * CanUseForCompany constructor.
     *
     * @param \Magento\CompanyPayment\Model\Config $config
     */
    public function __construct(
        \Magento\CompanyPayment\Model\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Is payment method available for company.
     *
     * @param string $paymentMethodCode
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return bool
     */
    public function isAvailableForCompany(
        $paymentMethodCode,
        \Magento\Company\Api\Data\CompanyInterface $company
    ) {
        $companyExtensionAttributes = $company->getExtensionAttributes();

        if ($companyExtensionAttributes->getUseConfigSettings() === $this->useBtob) {
            if ($companyExtensionAttributes->getApplicablePaymentMethod() === $this->useAll) {
                return true;
            }

            if ($companyExtensionAttributes->getApplicablePaymentMethod() === $this->useSpecific) {
                return $this->isMethodAvailable(
                    $paymentMethodCode,
                    $companyExtensionAttributes->getAvailablePaymentMethods()
                );
            }
        }

        return $this->isAvailableInB2bConfig($paymentMethodCode);
    }

    /**
     * Is method available in stores configuration.
     *
     * @param string $paymentMethodCode
     * @return bool
     */
    private function isAvailableInB2bConfig($paymentMethodCode)
    {
        return !$this->config->isSpecificApplicableMethodApplied()
        || $this->isMethodAvailable($paymentMethodCode, $this->config->getAvailablePaymentMethods());
    }

    /**
     * Check if method is available.
     *
     * @param string $paymentMethodCode
     * @param string $methods
     * @return bool
     */
    private function isMethodAvailable($paymentMethodCode, $methods)
    {
        return in_array($paymentMethodCode, explode(',', $methods));
    }
}
