<?php

namespace Magento\Company\Model;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Company\Model\ResourceModel\Customer as CustomerResource;

/**
 * A repository class for company entity that provides basic CRUD operations.
 */
class CompanyRepository implements CompanyRepositoryInterface
{
    /**
     * @var \Magento\Company\Api\Data\CompanyInterface[]
     */
    private $instances = [];

    /**
     * @var \Magento\Company\Api\Data\CompanyInterfaceFactory
     */
    private $companyFactory;

    /**
     * @var \Magento\Company\Model\Company\Delete
     */
    private $companyDeleter;

    /**
     * @var \Magento\Company\Model\Company\GetList
     */
    private $companyListGetter;

    /**
     * @var \Magento\Company\Model\Company\Save
     */
    private $companySaver;

    /**
     * @param \Magento\Company\Api\Data\CompanyInterfaceFactory $companyFactory
     * @param \Magento\Company\Model\Company\Delete $companyDeleter
     * @param \Magento\Company\Model\Company\GetList $companyListGetter
     * @param \Magento\Company\Model\Company\Save $companySaver
     */
    public function __construct(
        \Magento\Company\Api\Data\CompanyInterfaceFactory $companyFactory,
        \Magento\Company\Model\Company\Delete $companyDeleter,
        \Magento\Company\Model\Company\GetList $companyListGetter,
        \Magento\Company\Model\Company\Save $companySaver
    ) {
        $this->companyFactory = $companyFactory;
        $this->companyDeleter = $companyDeleter;
        $this->companyListGetter = $companyListGetter;
        $this->companySaver = $companySaver;
    }

    /**
     * @inheritdoc
     */
    public function save(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        unset($this->instances[$company->getId()]);
        $this->companySaver->save($company);
        return $company;
    }

    /**
     * @inheritdoc
     */
    public function get($companyId)
    {
        if (!isset($this->instances[$companyId])) {
            /** @var Company $company */
            $company = $this->companyFactory->create();
            $company->load($companyId);
            if (!$company->getId()) {
                throw NoSuchEntityException::singleField('id', $companyId);
            }
            $this->instances[$companyId] = $company;
        }
        return $this->instances[$companyId];
    }

    /**
     * @inheritdoc
     */
    public function delete(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        $companyId = $company->getId();
        try {
            unset($this->instances[$companyId]);
            $this->companyDeleter->delete($company);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __(
                    'Cannot delete company with id %1',
                    $companyId
                ),
                $e
            );
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($companyId)
    {
        $company = $this->get($companyId);
        return $this->delete($company);
    }

    /**
     * @inheritdoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        return $this->companyListGetter->getList($criteria);
    }
}
