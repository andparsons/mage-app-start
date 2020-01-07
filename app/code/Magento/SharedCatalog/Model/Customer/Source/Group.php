<?php

namespace Magento\SharedCatalog\Model\Customer\Source;

/**
 * Prepare data for the customer groups list.
 */
class Group implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\SharedCatalog\Model\Customer\Source\Collection\GroupFactory
     */
    private $groupCollectionFactory;

    /**
     * @param \Magento\SharedCatalog\Model\Customer\Source\Collection\GroupFactory $groupCollectionFactory
     */
    public function __construct(
        \Magento\SharedCatalog\Model\Customer\Source\Collection\GroupFactory $groupCollectionFactory
    ) {
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Customer Groups'), 'value' => $this->getCustomerGroups()],
            ['label' => __('Shared Catalogs'), 'value' => $this->getCustomerGroups(true)],
        ];
    }

    /**
     * Prepare data for the customer group dropdown.
     *
     * Prepare list of the customer groups connected with the shared catalog if $catalogGroups = true. Name of the
     * customer group is replaced by the name of the appropriate shared catalog. Customer group Not Logged In will be
     * removed from the list if $excludeNotLogged = true
     *
     * @param bool $catalogGroups [optional]
     * @param bool $excludeNotLogged [optional]
     * @return array
     */
    protected function getCustomerGroups($catalogGroups = false, $excludeNotLogged = true)
    {
        /**
         * @var \Magento\SharedCatalog\Model\Customer\Source\Collection\Group $collection
         */
        $collection = $this->groupCollectionFactory->create();
        $collection->joinSharedCatalogTable($catalogGroups, $excludeNotLogged);
        $options = [];

        foreach ($collection as $group) {
            $sharedCatalogName = $group->getSharedCatalogName();
            $options[] = [
                'label' => $sharedCatalogName ?: $group->getCode(),
                'value' => $group->getId(),
                '__disableTmpl' => true,
            ];
        }
        return $options;
    }
}
