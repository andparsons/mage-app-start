<?php
namespace Magento\Solr\Model;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Enterprise search model observer
 */
class Observer
{
    /**
     * Eav entity attribute option coll factory
     *
     * @var CollectionFactory
     */
    protected $_eavEntityAttributeOptionCollectionFactory = null;

    /**
     * Search data
     *
     * @var \Magento\Solr\Helper\Data
     */
    protected $_searchData = null;

    /**
     * @param CollectionFactory $eavEntityAttributeOptionCollectionFactory
     * @param \Magento\Solr\Helper\Data $searchData
     */
    public function __construct(
        CollectionFactory $eavEntityAttributeOptionCollectionFactory,
        \Magento\Solr\Helper\Data $searchData
    ) {
        $this->_eavEntityAttributeOptionCollectionFactory = $eavEntityAttributeOptionCollectionFactory;
        $this->_searchData = $searchData;
    }

    /**
     * Store searchable attributes at adapter to avoid new collection load there
     *
     * @param EventObserver $observer
     * @return void
     */
    public function storeSearchableAttributes(EventObserver $observer)
    {
        /** @var \Magento\CatalogSearch\Model\ResourceModel\EngineInterface $engine */
        $engine = $observer->getEvent()->getEngine();
        /** @var \Magento\Eav\Model\Entity\Attribute[] $attributes */
        $attributes = $observer->getEvent()->getAttributes();
        if (!$engine || !$attributes) {
            return;
        }

        foreach ($attributes as $attribute) {
            if (!$attribute->usesSource()) {
                continue;
            }

            $optionCollection = $this->_eavEntityAttributeOptionCollectionFactory->create()->setAttributeFilter(
                $attribute->getAttributeId()
            )->setPositionOrder(
                \Magento\Framework\DB\Select::SQL_ASC,
                true
            )->load();

            $optionsOrder = [];
            foreach ($optionCollection as $option) {
                /** @var \Magento\Eav\Model\Entity\Attribute\Option $option */
                $optionsOrder[] = $option->getOptionId();
            }
            $optionsOrder = array_flip($optionsOrder);

            $attribute->setOptionsOrder($optionsOrder);
        }

        $engine->storeSearchableAttributes($attributes);
    }
}
