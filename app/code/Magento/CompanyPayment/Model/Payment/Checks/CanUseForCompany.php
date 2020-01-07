<?php

namespace Magento\CompanyPayment\Model\Payment\Checks;

use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

/**
 * Class CanUseForCompany.
 */
class CanUseForCompany implements \Magento\Payment\Model\Checks\SpecificationInterface
{
    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var \Magento\CompanyPayment\Model\Payment\AvailabilityChecker
     */
    private $availabilityChecker;

    /**
     * CanUseForCompany constructor.
     *
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     * @param \Magento\CompanyPayment\Model\Payment\AvailabilityChecker $availabilityChecker
     */
    public function __construct(
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Magento\CompanyPayment\Model\Payment\AvailabilityChecker $availabilityChecker
    ) {
        $this->companyManagement = $companyManagement;
        $this->availabilityChecker = $availabilityChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(MethodInterface $paymentMethod, Quote $quote)
    {
        if (!$quote->getCustomerId()) {
            return true;
        }

        $company = $this->companyManagement->getByCustomerId($quote->getCustomerId());

        if (!$company) {
            return true;
        }

        return $this->availabilityChecker->isAvailableForCompany($paymentMethod->getCode(), $company);
    }
}
