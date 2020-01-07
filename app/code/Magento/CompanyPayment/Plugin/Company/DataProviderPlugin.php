<?php

namespace Magento\CompanyPayment\Plugin\Company;

/**
 * Class DataProviderPlugin.
 */
class DataProviderPlugin
{
    /**
     * Around get settings data plugin.
     *
     * @param \Magento\Company\Model\Company\DataProvider $subject
     * @param \Closure $proceed
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetSettingsData(
        \Magento\Company\Model\Company\DataProvider $subject,
        $proceed,
        \Magento\Company\Api\Data\CompanyInterface $company
    ) {
        $extensionAttributes = $company->getExtensionAttributes();

        $settings = [
            'extension_attributes' => [
                'applicable_payment_method' => $extensionAttributes->getApplicablePaymentMethod(),
                'available_payment_methods' => $extensionAttributes->getAvailablePaymentMethods(),
                'use_config_settings' => $extensionAttributes->getUseConfigSettings(),
            ]
        ];

        $originalSettings = $proceed($company);

        return array_replace_recursive($originalSettings, $settings);
    }
}
