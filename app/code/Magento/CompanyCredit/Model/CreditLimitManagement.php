<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Model;

use Magento\CompanyCredit\Api\CreditLimitManagementInterface;
use Magento\CompanyCredit\Model\ResourceModel\CreditLimit as CreditLimitResource;
use Magento\CompanyCredit\Api\Data\CreditLimitInterface;
use Magento\CompanyCredit\Model\ResourceModel\CreditLimit\CollectionFactory as CreditLimitCollectionFactory;

/**
 * Management the credit limit for a specified company.
 */
class CreditLimitManagement implements CreditLimitManagementInterface
{
    /**
     * @var \Magento\CompanyCredit\Model\CreditLimitFactory
     */
    private $creditLimitFactory;

    /**
     * @var \Magento\CompanyCredit\Model\ResourceModel\CreditLimit
     */
    private $creditLimitResource;

    /**
     * CreditLimitRepository constructor.
     *
     * @param \Magento\CompanyCredit\Model\CreditLimitFactory $creditLimitFactory
     * @param CreditLimitResource $creditLimitResource
     */
    public function __construct(
        \Magento\CompanyCredit\Model\CreditLimitFactory $creditLimitFactory,
        CreditLimitResource $creditLimitResource
    ) {
        $this->creditLimitFactory = $creditLimitFactory;
        $this->creditLimitResource = $creditLimitResource;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditByCompanyId($companyId)
    {
        /** @var \Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit */
        $creditLimit = $this->creditLimitFactory->create();
        $this->creditLimitResource->load($creditLimit, $companyId, CreditLimitInterface::COMPANY_ID);
        if (!$creditLimit->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __(
                    'Requested company is not found. Row ID: %fieldName = %fieldValue.',
                    ['fieldName' => 'CompanyID', 'fieldValue' => $companyId]
                )
            );
        }
        return $creditLimit;
    }
}
