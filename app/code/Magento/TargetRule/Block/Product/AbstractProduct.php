<?php
namespace Magento\TargetRule\Block\Product;

/**
 * TargetRule abstract Products Block
 *
 */
abstract class AbstractProduct extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * Link collection
     *
     * @var null|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_linkCollection = null;

    /**
     * Catalog Product List Item Collection array
     *
     * @var null|array
     */
    protected $_items = null;

    /**
     * Get link collection for specific target
     *
     * @abstract
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    abstract protected function _getTargetLinkCollection();

    /**
     * Get target rule products
     *
     * @abstract
     * @return array
     */
    abstract protected function _getTargetRuleProducts();

    /**
     * Retrieve Catalog Product List Type identifier
     *
     * @return int
     */
    abstract public function getProductListType();

    /**
     * Retrieve Maximum Number Of Product
     *
     * @return int
     */
    abstract public function getPositionLimit();

    /**
     * Retrieve Position Behavior
     *
     * @return int
     */
    abstract public function getPositionBehavior();

    /**
     * Target rule data
     *
     * @var \Magento\TargetRule\Helper\Data
     */
    protected $_targetRuleData = null;

    /**
     * @var \Magento\TargetRule\Model\ResourceModel\Index
     */
    protected $_resourceIndex;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\TargetRule\Model\ResourceModel\Index $index
     * @param \Magento\TargetRule\Helper\Data $targetRuleData
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\TargetRule\Model\ResourceModel\Index $index,
        \Magento\TargetRule\Helper\Data $targetRuleData,
        array $data = []
    ) {
        $this->_resourceIndex = $index;
        $this->_targetRuleData = $targetRuleData;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Return the behavior positions applicable to products based on the rule(s)
     *
     * @return int[]
     */
    public function getRuleBasedBehaviorPositions()
    {
        return [
            \Magento\TargetRule\Model\Rule::BOTH_SELECTED_AND_RULE_BASED,
            \Magento\TargetRule\Model\Rule::RULE_BASED_ONLY
        ];
    }

    /**
     * Retrieve the behavior positions applicable to selected products
     *
     * @return int[]
     */
    public function getSelectedBehaviorPositions()
    {
        return [
            \Magento\TargetRule\Model\Rule::BOTH_SELECTED_AND_RULE_BASED,
            \Magento\TargetRule\Model\Rule::SELECTED_ONLY
        ];
    }

    /**
     * Get link collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|null
     */
    public function getLinkCollection()
    {
        if ($this->_linkCollection === null) {
            $this->_linkCollection = $this->_getTargetLinkCollection();

            if ($this->_linkCollection) {
                // Perform rotation mode
                $select = $this->_linkCollection->getSelect();
                $rotationMode = $this->_targetRuleData->getRotationMode($this->getProductListType());
                if ($rotationMode == \Magento\TargetRule\Model\Rule::ROTATION_SHUFFLE) {
                    $this->_resourceIndex->orderRand($select);
                } else {
                    $select->order('link_attribute_position_int.value ASC');
                }
            }
        }

        return $this->_linkCollection;
    }

    /**
     * Get linked products
     *
     * @return array
     */
    protected function _getLinkProducts()
    {
        $items = [];
        $linkCollection = $this->getLinkCollection();
        if ($linkCollection) {
            foreach ($linkCollection as $item) {
                $items[$item->getEntityId()] = $item;
            }
        }
        return $items;
    }

    /**
     * Whether rotation mode is set to "shuffle"
     *
     * @return bool
     */
    public function isShuffled()
    {
        $rotationMode = $this->_targetRuleData->getRotationMode($this->getProductListType());
        return $rotationMode == \Magento\TargetRule\Model\Rule::ROTATION_SHUFFLE;
    }

    /**
     * Order product items
     *
     * @param array $items
     * @return array
     */
    protected function _orderProductItems(array $items)
    {
        if ($this->isShuffled()) {
            // shuffling assoc
            $ids = array_keys($items);
            shuffle($ids);
            $result = [];
            foreach ($ids as $id) {
                $result[$id] = $items[$id];
            }
            return $result;
        } else {
            uasort($items, [$this, 'compareItems']);
            return $items;
        }
    }

    /**
     * Compare two items for ordered list
     *
     * @param \Magento\Framework\DataObject $item1
     * @param \Magento\Framework\DataObject $item2
     * @return int
     */
    public function compareItems($item1, $item2)
    {
        // Prevent rule-based items to have any position
        if ($item2->getPosition() === null && $item1->getPosition() !== null) {
            return -1;
        } elseif ($item1->getPosition() === null && $item2->getPosition() !== null) {
            return 1;
        }
        $positionDiff = (int)$item1->getPosition() - (int)$item2->getPosition();
        if ($positionDiff != 0) {
            return $positionDiff;
        }
        return (int)$item1->getEntityId() - (int)$item2->getEntityId();
    }

    /**
     * Slice items to limit
     *
     * @return $this
     */
    protected function _sliceItems()
    {
        if ($this->_items !== null) {
            if ($this->isShuffled()) {
                $this->_items = array_slice($this->_items, 0, $this->_targetRuleData->getMaxProductsListResult(), true);
            } else {
                $this->_items = array_slice($this->_items, 0, $this->getPositionLimit(), true);
            }
        }
        return $this;
    }

    /**
     * Retrieve Catalog Product List Items
     *
     * @return array
     */
    public function getItemCollection()
    {
        if ($this->_items === null) {
            $behavior = $this->getPositionBehavior();

            $this->_items = [];

            if (in_array($behavior, $this->getSelectedBehaviorPositions())) {
                $this->_items = $this->_orderProductItems($this->_getLinkProducts());
            }

            if (in_array($behavior, $this->getRuleBasedBehaviorPositions())) {
                foreach ($this->_orderProductItems($this->_getTargetRuleProducts()) as $id => $item) {
                    $this->_items[$id] = $item;
                }
            }
            $this->_sliceItems();
        }

        return $this->_items;
    }
}
