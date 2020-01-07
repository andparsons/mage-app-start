<?php
namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;

/**
 * Interface \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerInterface
 *
 */
interface HandlerInterface
{
    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function handle(\Magento\Catalog\Model\Product $product);
}
