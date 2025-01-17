<?php

require __DIR__ . '/../../../Magento/Catalog/_files/multiple_products_rollback.php';

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->load(3);
if ($product->getId()) {
    $product->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
