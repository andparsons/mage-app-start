<?php

namespace Magento\CompanyPayment\Plugin\Company\Model\Customer;

/**
 * Class CompanyPlugin.
 */
class CompanyPlugin
{
    /**
     * @var \Magento\CompanyPayment\Model\ResourceModel\CompanyPaymentMethod
     */
    private $companyPaymentMethodResource;

    /**
     * @var \Magento\CompanyPayment\Model\CompanyPaymentMethodFactory
     */
    private $companyPaymentMethodFactory;

    /**
     * @var \Magento\CompanyPayment\Model\Config
     */
    private $config;

    /**
     * B2B payment settings default value
     *
     * @var string
     */
    private $btobPaymentSettingsDefaultValue = '0';

    /**
     * Use config settings default value.
     *
     * @var string
     */
    private $useConfigSettingsDefaultValue = '1';

    /**
     * CompanyPaymentMethods constructor.
     *
     * @param \Magento\CompanyPayment\Model\ResourceModel\CompanyPaymentMethod $companyPaymentMethodResource
     * @param \Magento\CompanyPayment\Model\CompanyPaymentMethodFactory $companyPaymentMethodFactory
     * @param \Magento\CompanyPayment\Model\Config $config
     */
    public function __construct(
        \Magento\CompanyPayment\Model\ResourceModel\CompanyPaymentMethod $companyPaymentMethodResource,
        \Magento\CompanyPayment\Model\CompanyPaymentMethodFactory $companyPaymentMethodFactory,
        \Magento\CompanyPayment\Model\Config $config
    ) {
        $this->companyPaymentMethodResource = $companyPaymentMethodResource;
        $this->companyPaymentMethodFactory = $companyPaymentMethodFactory;
        $this->config = $config;
    }

    /**
     * After create company plugin
     *
     * @param \Magento\Company\Model\Customer\Company $subject
     * @param \Magento\Company\Api\Data\CompanyInterface $result
     * @return \Magento\Company\Api\Data\CompanyInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateCompany(
        \Magento\Company\Model\Customer\Company $subject,
        \Magento\Company\Api\Data\CompanyInterface $result
    ) {
        /** @var \Magento\CompanyPayment\Model\CompanyPaymentMethod $paymentSettings */
        $paymentSettings = $this->companyPaymentMethodFactory->create();
        $paymentSettings->setCompanyId($result->getId());
        $paymentSettings->setApplicablePaymentMethod($this->btobPaymentSettingsDefaultValue);
        $paymentSettings->setUseConfigSettings($this->useConfigSettingsDefaultValue);

        if ($this->config->isSpecificApplicableMethodApplied() && $this->config->getAvailablePaymentMethods()) {
            $paymentSettings->setAvailablePaymentMethods($this->config->getAvailablePaymentMethods());
        }

        $this->companyPaymentMethodResource->save($paymentSettings);

        return $result;
    }
}
