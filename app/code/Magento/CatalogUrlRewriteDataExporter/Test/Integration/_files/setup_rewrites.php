<?php

use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\UrlRewrite;

require __DIR__ . '/../../../../CatalogDataExporter/Test/Integration/_files/setup_simple_products.php';

$store = Bootstrap::getObjectManager()->create(Store::class);
$storeId = $store->load('fixture_second_store', 'code')->getId();
/** @var \Magento\UrlRewrite\Model\ResourceModel\UrlRewrite $rewriteResource */
$rewriteResource = $objectManager->create(\Magento\UrlRewrite\Model\ResourceModel\UrlRewrite::class);
$rewrite = $objectManager->create(UrlRewrite::class);
$rewrite->setEntityType('product')
    ->setEntityId(10)
    ->setRequestPath('simple-product1.html')
    ->setTargetPath('catalog/product/view/id/10')
    ->setRedirectType(0)
    ->setStoreId($storeId);
$rewriteResource->save($rewrite);

$rewrite = $objectManager->create(UrlRewrite::class);
/** @var \Magento\UrlRewrite\Model\ResourceModel\UrlRewrite $rewriteResource */
$rewriteResource = $objectManager->create(\Magento\UrlRewrite\Model\ResourceModel\UrlRewrite::class);
$rewrite->setEntityType('product')
    ->setEntityId(11)
    ->setRequestPath('simple-product2.html')
    ->setTargetPath('catalog/product/view/id/11')
    ->setRedirectType(0)
    ->setStoreId($storeId);
$rewriteResource->save($rewrite);

$rewrite = $objectManager->create(UrlRewrite::class);
$rewrite->setEntityType('product')
    ->setEntityId(12)
    ->setRequestPath('simple-product3.html')
    ->setTargetPath('catalog/product/view/id/12')
    ->setRedirectType(0)
    ->setStoreId($storeId);
$rewriteResource->save($rewrite);
