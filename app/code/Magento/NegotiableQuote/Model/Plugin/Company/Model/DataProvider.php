<?php

namespace Magento\NegotiableQuote\Model\Plugin\Company\Model;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\Company\DataProvider as CompanyDataProvider;
use Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterface;
use Magento\NegotiableQuote\Helper\Company as CompanyHelper;

/**
 * Class DataProvider.
 */
class DataProvider
{
    /**
     * @var CompanyHelper
     */
    private $companyHelper;

    /**
     * @param CompanyHelper $companyHelper
     */
    public function __construct(
        CompanyHelper $companyHelper
    ) {
        $this->companyHelper = $companyHelper;
    }

    /**
     * Around getSettingsData.
     *
     * @param CompanyDataProvider $subject
     * @param \Closure $proceed
     * @param CompanyInterface $company
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetSettingsData(CompanyDataProvider $subject, \Closure $proceed, CompanyInterface $company)
    {
        $result = $proceed($company);
        /** @var CompanyQuoteConfigInterface $companyQuoteConfig */
        if ($company) {
            $companyQuoteConfig = $this->companyHelper->getQuoteConfig($company);
            $result[CompanyQuoteConfigInterface::IS_QUOTE_ENABLED] = $companyQuoteConfig->getIsQuoteEnabled();
        }
        return $result;
    }
}
