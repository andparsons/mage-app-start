<?php
declare(strict_types=1);

/**
 * Remove all products as strategy of isolation process
 */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product */
$productCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Catalog\Model\Product')
    ->getCollection();

foreach ($productCollection as $product) {
    $product->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
