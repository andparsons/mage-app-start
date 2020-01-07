<?php

namespace Magento\Company\Model\ResourceModel\Order;

/**
 * Class CollectionFactory.
 */
class CollectionFactory implements \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface
{
    /**
     * Object Manager instance.
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Instance name to create.
     *
     * @var string
     */
    private $instanceName = \Magento\Sales\Model\ResourceModel\Order\Collection::class;

    /**
     * @var \Magento\Company\Model\Company\Structure
     */
    private $structure;

    /**
     * @var \Magento\Company\Api\StatusServiceInterface
     */
    private $moduleConfig;

    /**
     * CollectionFactory constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Company\Model\Company\Structure $structure
     * @param \Magento\Company\Api\StatusServiceInterface $moduleConfig
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Company\Model\Company\Structure $structure,
        \Magento\Company\Api\StatusServiceInterface $moduleConfig
    ) {
        $this->objectManager = $objectManager;
        $this->structure = $structure;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function create($customerId = null)
    {
        $collection = $this->objectManager->create($this->instanceName);

        if ($customerId) {
            $customers = [];

            if ($this->moduleConfig->isActive()) {
                $customers = $this->structure->getAllowedChildrenIds($customerId);
            }

            $customers[] = $customerId;
            $collection->addFieldToFilter('customer_id', ['in' => $customers]);
        }

        return $collection;
    }
}
