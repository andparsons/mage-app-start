<?php
namespace Magento\Company\Model\Company\Source;

use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magento\User\Api\Data\UserInterface;

/**
 * Class Status
 */
class SalesRepresentatives implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * Constructor
     *
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $companyCollectionFactory
     */
    public function __construct(
        CollectionFactory $companyCollectionFactory
    ) {
        $this->collection = $companyCollectionFactory->create();
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $items = $this->collection->getItems();
        $options = [];
        /** @var UserInterface $adminUser */
        foreach ($items as $adminUser) {
            $options[] = ['label' => $adminUser->getUserName(), 'value' => $adminUser->getId()];
        }
        return $options;
    }
}
