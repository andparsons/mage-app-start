<?php

namespace Magento\CompanyCredit\Model;

use Magento\CompanyCredit\Api\CreditDataProviderInterface;
use Magento\CompanyCredit\Api\CreditLimitManagementInterface;
use Magento\CompanyCredit\Model\CreditDataFactory;

/**
 * Class CreditDataProvider.
 */
class CreditDataProvider implements CreditDataProviderInterface
{
    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitManagementInterface
     */
    private $creditLimitManagement;

    /**
     * @var \Magento\CompanyCredit\Model\CreditDataFactory
     */
    private $creditDataFactory;

    /**
     * Constructor.
     *
     * @param CreditLimitManagementInterface $creditLimitManagement
     * @param \Magento\CompanyCredit\Model\CreditDataFactory $creditDataFactory
     */
    public function __construct(
        CreditLimitManagementInterface $creditLimitManagement,
        CreditDataFactory $creditDataFactory
    ) {
        $this->creditLimitManagement = $creditLimitManagement;
        $this->creditDataFactory = $creditDataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get($companyId)
    {
        $creditObject = $this->creditLimitManagement->getCreditByCompanyId($companyId);
        return $this->creditDataFactory->create(['credit' => $creditObject]);
    }
}
