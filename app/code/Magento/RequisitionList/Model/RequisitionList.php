<?php

namespace Magento\RequisitionList\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\RequisitionList\Api\Data\RequisitionListInterface;
use Magento\RequisitionList\Model\RequisitionList\ItemsLoadHandler;

/**
 * Requisition List Model
 */
class RequisitionList extends AbstractExtensibleModel implements RequisitionListInterface
{
    /**
     * @var ItemsLoadHandler
     */
    private $itemsLoadHandler;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param ItemsLoadHandler $itemsLoadHandler
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ItemsLoadHandler $itemsLoadHandler,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->itemsLoadHandler = $itemsLoadHandler;
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\RequisitionList\Model\ResourceModel\RequisitionList::class);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($requisitionListId)
    {
        return $this->setData(RequisitionListInterface::REQUISITION_LIST_ID, $requisitionListId);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(RequisitionListInterface::REQUISITION_LIST_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(RequisitionListInterface::CUSTOMER_ID, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(RequisitionListInterface::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(RequisitionListInterface::NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(RequisitionListInterface::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        return $this->setData(RequisitionListInterface::DESCRIPTION, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->getData(RequisitionListInterface::DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($time)
    {
        return $this->setData(RequisitionListInterface::UPDATED_AT, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(RequisitionListInterface::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setItems(array $items)
    {
        return $this->setData(RequisitionListInterface::ITEMS, $items);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if (!is_array($this->getData(RequisitionListInterface::ITEMS))) {
            $this->itemsLoadHandler->load($this);
        }
        return (array)$this->getData(RequisitionListInterface::ITEMS);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Magento\RequisitionList\Api\Data\RequisitionListExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
