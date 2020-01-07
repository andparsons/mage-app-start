<?php

namespace Magento\SharedCatalog\Model\Indexer;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Indexer\DimensionProviderInterface;

class CustomerGroupDataProvider implements DimensionProviderInterface
{
    /**
     * Name for customer group dimension for multidimensional indexer
     * 'cg' - stands for 'customer_group'
     */
    const DIMENSION_NAME = 'cg';

    /**
     * @var GroupManagementInterface
     */
    private $groupManagement;

    /**
     * @var \SplFixedArray
     */
    private $customerGroupsDataIterator;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var
     */
    private $customerGroup;

    /**
     * @param GroupManagementInterface $groupManagement
     * @param DimensionFactory $dimensionFactory
     * @param $customerGroup
     */
    public function __construct(
        GroupManagementInterface $groupManagement,
        DimensionFactory $dimensionFactory,
        GroupInterface $customerGroup
    ) {
        $this->dimensionFactory = $dimensionFactory;
        $this->groupManagement = $groupManagement;
        $this->customerGroup = $customerGroup;
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->getCustomerGroups() as $customerGroup) {
            yield $this->dimensionFactory->create(self::DIMENSION_NAME, (string)$customerGroup);
        }
    }

    /**
     * @return \SplFixedArray
     */
    private function getCustomerGroups()
    {
        if ($this->customerGroupsDataIterator === null) {
            $this->customerGroupsDataIterator = \SplFixedArray::fromArray(
                [$this->customerGroup->getId()],
                false
            );
        }

        return $this->customerGroupsDataIterator;
    }
}
