<?php

namespace Magento\CompanyPayment\Model\Company\SaveHandler;

/**
 * Class AvailablePaymentMethods.
 */
class AvailablePaymentMethods implements \Magento\Company\Model\SaveHandlerInterface
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
     * Company payment settings field.
     *
     * @var array
     */
    private $companyPaymentSettings = [
        'applicable_payment_method',
        'available_payment_methods',
        'use_config_settings'
    ];

    /**
     * CompanyPaymentMethods constructor.
     *
     * @param \Magento\CompanyPayment\Model\ResourceModel\CompanyPaymentMethod $companyPaymentMethodResource
     * @param \Magento\CompanyPayment\Model\CompanyPaymentMethodFactory $companyPaymentMethodFactory
     */
    public function __construct(
        \Magento\CompanyPayment\Model\ResourceModel\CompanyPaymentMethod $companyPaymentMethodResource,
        \Magento\CompanyPayment\Model\CompanyPaymentMethodFactory $companyPaymentMethodFactory
    ) {
        $this->companyPaymentMethodResource = $companyPaymentMethodResource;
        $this->companyPaymentMethodFactory = $companyPaymentMethodFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(
        \Magento\Company\Api\Data\CompanyInterface $company,
        \Magento\Company\Api\Data\CompanyInterface $initialCompany
    ) {
        $needSave = false;
        $extensionAttributes = $company->getExtensionAttributes();
        $initialExtensionAttributes = $initialCompany->getExtensionAttributes();

        foreach ($this->companyPaymentSettings as $companyPaymentSetting) {
            $method = 'get'
                . \Magento\Framework\Api\SimpleDataObjectConverter::snakeCaseToUpperCamelCase($companyPaymentSetting);
            $result = $extensionAttributes->$method();
            $initialResult = $initialExtensionAttributes->$method();

            if (is_array($result)) {
                $result = implode(',', $result);
            }

            if ($result && $result !== $initialResult) {
                $needSave = true;
                break;
            }
        }

        if ($needSave) {
            $this->savePaymentSettings($company);
        }

        $company->setExtensionAttributes($this->erasePaymentSettingsData($extensionAttributes));
    }

    /**
     * Save payment settings.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @throws \Exception
     * @return void
     */
    private function savePaymentSettings(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        /** @var \Magento\CompanyPayment\Model\CompanyPaymentMethod $paymentSettings */
        $paymentSettings = $this->companyPaymentMethodFactory->create()->load($company->getId());
        $extensionAttributes = $company->getExtensionAttributes();

        if (!$paymentSettings->getId()) {
            $paymentSettings->setCompanyId($company->getId());
        }

        $availableMethods = is_array($extensionAttributes->getAvailablePaymentMethods()) ?
            implode(',', $extensionAttributes->getAvailablePaymentMethods())
            : '';

        $paymentSettings->setApplicablePaymentMethod($extensionAttributes->getApplicablePaymentMethod());
        $paymentSettings->setAvailablePaymentMethods($availableMethods);
        $paymentSettings->setUseConfigSettings($extensionAttributes->getUseConfigSettings());

        $this->companyPaymentMethodResource->save($paymentSettings);
    }

    /**
     * Erase saved attributes to prevent breaking of populateWithArray.
     *
     * @param \Magento\Company\Api\Data\CompanyExtensionInterface $extensionAttributes
     * @return \Magento\Company\Api\Data\CompanyExtensionInterface
     */
    private function erasePaymentSettingsData(\Magento\Company\Api\Data\CompanyExtensionInterface $extensionAttributes)
    {
        foreach ($this->companyPaymentSettings as $companyPaymentSetting) {
            $method = 'set'
                . \Magento\Framework\Api\SimpleDataObjectConverter::snakeCaseToUpperCamelCase($companyPaymentSetting);
            $extensionAttributes->$method(null);
        }

        return $extensionAttributes;
    }
}
