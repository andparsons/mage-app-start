<?php

namespace Magento\NegotiableQuote\Model\Plugin\Company\Model;

use Magento\NegotiableQuote\Helper\Company as CompanyHelper;

/**
 * Class Company
 */
class Company
{
    /**
     * @var CompanyHelper
     */
    protected $companyHelper;

    /**
     * @param CompanyHelper $companyHelper
     */
    public function __construct(
        CompanyHelper $companyHelper
    ) {
        $this->companyHelper = $companyHelper;
    }

    /**
     * @param \Magento\Company\Api\Data\CompanyInterface $subject
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return \Magento\Company\Api\Data\CompanyInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoad(
        \Magento\Company\Api\Data\CompanyInterface $subject,
        \Magento\Company\Api\Data\CompanyInterface $company
    ) {
        $this->companyHelper->loadQuoteConfig($company);
        return $company;
    }
}
