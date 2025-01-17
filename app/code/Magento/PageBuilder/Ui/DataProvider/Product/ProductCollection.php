<?php
declare(strict_types=1);

namespace Magento\PageBuilder\Ui\DataProvider\Product;

use Magento\Store\Model\Store;

/**
 * PageBuilder ProductCollection class for allowing store-agnostic collections
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ProductCollection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * @inheritdoc
     */
    public function setVisibility($visibility)
    {
        if ($this->getStoreId() === Store::DEFAULT_STORE_ID) {
            $this->addAttributeToFilter('visibility', $visibility);
        } else {
            parent::setVisibility($visibility);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _productLimitationJoinPrice()
    {
        $this->_productLimitationFilters->setUsePriceIndex($this->getStoreId() !== Store::DEFAULT_STORE_ID);
        return $this->_productLimitationPrice(false);
    }
}
