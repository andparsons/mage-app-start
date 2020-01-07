<?php
namespace Magento\CatalogUrlRewrite\Model\Category;

use Magento\Framework\Model\AbstractModel;

class Product extends AbstractModel
{
    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\CatalogUrlRewrite\Model\ResourceModel\Category\Product::class);
    }
}
