<?php
namespace Magento\CheckoutStaging\Model\ResourceModel\PreviewQuota;

use Magento\CheckoutStaging\Model;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(Model\PreviewQuota::class, Model\ResourceModel\PreviewQuota::class);
    }
}
