<?php

namespace Magento\NegotiableQuote\Plugin\Company\Model;

/**
 * Class StructurePlugin
 */
class StructurePlugin
{
    /**
     * StructurePlugin constructor.
     *
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * Clear not existing users.
     *
     * @param \Magento\Company\Model\Company\Structure $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllowedIds(
        \Magento\Company\Model\Company\Structure $subject,
        array $result
    ) {
        if (!empty($result['users'])) {
            $result['users'] = $this->filterExistingCustomers($result['users']);
        }

        return $result;
    }

    /**
     * Clear not existing company members.
     *
     * @param \Magento\Company\Model\Company\Structure $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllowedChildrenIds(
        \Magento\Company\Model\Company\Structure $subject,
        array $result
    ) {
        return $this->filterExistingCustomers($result);
    }

    /**
     * Filter all existing customers.
     *
     * @param array $allChildrenIds
     * @return array
     */
    private function filterExistingCustomers(array $allChildrenIds)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', $allChildrenIds, 'in')
            ->create();
        $existingCustomers = $this->customerRepositoryInterface->getList($searchCriteria)->getItems();
        $existingChildrenIds = [];

        foreach ($existingCustomers as $existingCustomer) {
            $existingChildrenIds[] = $existingCustomer->getId();
        }

        return $existingChildrenIds;
    }
}
