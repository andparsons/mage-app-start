<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Model;

/**
 * Credit limit repository for CRUD operations.
 */
class CreditLimitRepository implements \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface
{
    /**
     * @var \Magento\CompanyCredit\Api\Data\CreditLimitInterface[]
     */
    private $instancesById = [];

    /**
     * @var \Magento\CompanyCredit\Model\CreditLimitFactory
     */
    private $creditLimitFactory;

    /**
     * @var \Magento\CompanyCredit\Model\ResourceModel\CreditLimit
     */
    private $creditLimitResource;

    /**
     * @var \Magento\CompanyCredit\Model\SaveHandler
     */
    private $saveHandler;

    /**
     * @var \Magento\CompanyCredit\Model\Validator
     */
    private $validator;

    /**
     * @var \Magento\CompanyCredit\Model\CreditLimit\SearchProvider
     */
    private $searchProvider;

    /**
     * @param \Magento\CompanyCredit\Model\CreditLimitFactory $creditLimitFactory
     * @param \Magento\CompanyCredit\Model\ResourceModel\CreditLimit $creditLimitResource
     * @param \Magento\CompanyCredit\Model\Validator $validator
     * @param \Magento\CompanyCredit\Model\SaveHandler $saveHandler
     * @param \Magento\CompanyCredit\Model\CreditLimit\SearchProvider $companyCreditSearchProvider
     */
    public function __construct(
        \Magento\CompanyCredit\Model\CreditLimitFactory $creditLimitFactory,
        \Magento\CompanyCredit\Model\ResourceModel\CreditLimit $creditLimitResource,
        \Magento\CompanyCredit\Model\Validator $validator,
        \Magento\CompanyCredit\Model\SaveHandler $saveHandler,
        \Magento\CompanyCredit\Model\CreditLimit\SearchProvider $companyCreditSearchProvider
    ) {
        $this->creditLimitFactory = $creditLimitFactory;
        $this->creditLimitResource = $creditLimitResource;
        $this->validator = $validator;
        $this->saveHandler = $saveHandler;
        $this->searchProvider = $companyCreditSearchProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit)
    {
        $prevCreditLimitData = $creditLimit->getData();
        $creditCurrencyChanged = false;
        $originalCreditLimit = null;
        if ($creditLimit->getId()) {
            $originalCreditLimit = $this->get($creditLimit->getId());
        }
        $this->validator->validateCreditData($prevCreditLimitData);
        if ($creditLimit->getId()) {
            $currencyFrom = $originalCreditLimit->getCurrencyCode();
            $currencyTo = $prevCreditLimitData[\Magento\CompanyCredit\Api\Data\CreditLimitInterface::CURRENCY_CODE];
            $creditCurrencyChanged = $currencyFrom != $currencyTo;
        }
        $this->saveHandler->execute($creditLimit);
        if ($creditCurrencyChanged) {
            $this->delete($originalCreditLimit);
        }
        return $creditLimit;
    }

    /**
     * {@inheritdoc}
     */
    public function get($creditId, $reload = false)
    {
        if (!isset($this->instancesById[$creditId]) || $reload) {
            /** @var \Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit */
            $creditLimit = $this->creditLimitFactory->create();
            $this->creditLimitResource->load($creditLimit, $creditId);
            $this->validator->checkCompanyCreditExist($creditLimit, $creditId);
            $this->instancesById[$creditId] = $creditLimit;
        }
        return $this->instancesById[$creditId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit)
    {
        try {
            $id = $creditLimit->getId();
            $this->creditLimitResource->delete($creditLimit);
            unset($this->instancesById[$id]);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __(
                    'Cannot delete credit limit with id %1',
                    $creditLimit->getId()
                ),
                $e
            );
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        return $this->searchProvider->getList($searchCriteria);
    }
}
