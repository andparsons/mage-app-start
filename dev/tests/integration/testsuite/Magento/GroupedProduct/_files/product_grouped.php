<?php

require realpath(__DIR__ . '/../../') . '/Catalog/_files/product_associated.php';
require realpath(__DIR__ . '/../../') . '/Catalog/_files/product_virtual_in_stock.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->isObjectNew(true);
$product->setTypeId(
    \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE
)->setAttributeSetId(
    4
)->setWebsiteIds(
    [1]
)->setName(
    'Grouped Product'
)->setSku(
    'grouped-product'
)->setPrice(
    100
)->setTaxClassId(
    0
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
);

$newLinks = [];
$productLinkFactory = $objectManager->get(\Magento\Catalog\Api\Data\ProductLinkInterfaceFactory::class);

/** @var \Magento\Catalog\Api\Data\ProductLinkInterface $productLink */
$productLink = $productLinkFactory->create();
$linkedProduct = $productRepository->getById(1);
$productLink->setSku($product->getSku())
    ->setLinkType('associated')
    ->setLinkedProductSku($linkedProduct->getSku())
    ->setLinkedProductType($linkedProduct->getTypeId())
    ->setPosition(1)
    ->getExtensionAttributes()
    ->setQty(1);
$newLinks[] = $productLink;

/** @var \Magento\Catalog\Api\Data\ProductLinkInterface $productLink */
$productLink = $productLinkFactory->create();
$linkedProduct = $productRepository->getById(21);
$productLink->setSku($product->getSku())
    ->setLinkType('associated')
    ->setLinkedProductSku($linkedProduct->getSku())
    ->setLinkedProductType($linkedProduct->getTypeId())
    ->setPosition(2)
    ->getExtensionAttributes()
    ->setQty(2);
$newLinks[] = $productLink;
$product->setProductLinks($newLinks);
$product->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$productRepository->save($product);

/** @var \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);

$categoryLinkManagement->assignProductToCategories(
    $product->getSku(),
    [2]
);
