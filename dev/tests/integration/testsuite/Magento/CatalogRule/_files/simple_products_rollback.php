<?php
declare(strict_types=1);

\Magento\TestFramework\Helper\Bootstrap::getInstance()->getInstance()->reinitialize();

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
foreach (['simple1', 'simple2'] as $sku) {
    try {
        $product = $productRepository->get($sku, false, null, true);
        $productRepository->delete($product);
    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        //Nothing to delete
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

require __DIR__ . '/attribute_rollback.php';
