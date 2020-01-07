<?php

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var ProductRepositoryInterface $productInterface */
$productInterface = $objectManager->create(ProductRepositoryInterface::class);

$product = $productInterface->get('simple_option_50');
if ($product->getId()) {
    $productInterface->delete($product);
}

$product = $productInterface->get('simple_option_60');
if ($product->getId()) {
    $productInterface->delete($product);
}

$product = $productInterface->get('simple_option_70');
if ($product->getId()) {
    $productInterface->delete($product);
}

$product = $productInterface->get('configurable1');
if ($product->getId()) {
    $productInterface->delete($product);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

require __DIR__ . '/setup_configurable_attribute_rollback.php';
require __DIR__ . '/../../../../CatalogDataExporter/Test/Integration/_files/setup_categories_rollback.php';
require __DIR__ . '/../../../../CatalogDataExporter/Test/Integration/_files/setup_stores_rollback.php';
