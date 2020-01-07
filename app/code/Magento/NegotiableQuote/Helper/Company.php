<?php

namespace Magento\NegotiableQuote\Helper;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterface;

class Company
{
    /**
     * @var \Magento\Company\Api\Data\CompanyExtensionFactory
     */
    protected $companyExtensionFactory;

    /**
     * @var \Magento\NegotiableQuote\Api\CompanyQuoteConfigManagementInterface
     */
    protected $quoteConfigManager;

    /**
     * @param \Magento\Company\Api\Data\CompanyExtensionFactory $companyExtensionFactory
     * @param \Magento\NegotiableQuote\Api\CompanyQuoteConfigManagementInterface $quoteConfigManager
     */
    public function __construct(
        \Magento\Company\Api\Data\CompanyExtensionFactory $companyExtensionFactory,
        \Magento\NegotiableQuote\Api\CompanyQuoteConfigManagementInterface $quoteConfigManager
    ) {
        $this->companyExtensionFactory = $companyExtensionFactory;
        $this->quoteConfigManager = $quoteConfigManager;
    }

    /**
     * Load company quote config
     * @param CompanyInterface $company
     * @return CompanyInterface
     */
    public function loadQuoteConfig(CompanyInterface $company)
    {
        $companyExtension = $company->getExtensionAttributes();
        if ($companyExtension === null) {
            $companyExtension = $this->companyExtensionFactory->create();
        }

        /** @var CompanyQuoteConfigInterface $quoteConfig */
        $quoteConfig = $companyExtension->getQuoteConfig();
        if ($quoteConfig === null) {
            $quoteConfig = $this->quoteConfigManager->getByCompanyId($company->getId());
            $companyExtension->setQuoteConfig($quoteConfig);
            $company->setExtensionAttributes($companyExtension);
        }

        return $company;
    }

    /**
     * Get company quote config
     * @param CompanyInterface $company
     * @return CompanyQuoteConfigInterface
     */
    public function getQuoteConfig(CompanyInterface $company)
    {
        $company = $this->loadQuoteConfig($company);
        return $company->getExtensionAttributes()->getQuoteConfig();
    }
}
