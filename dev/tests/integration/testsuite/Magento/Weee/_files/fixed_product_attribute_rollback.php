<?php

declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/* @var EavAttribute $attribute */
$attribute = $objectManager->get(\Magento\Eav\Model\Entity\Attribute::class);
$attribute->loadByCode(\Magento\Catalog\Model\Product::ENTITY, 'fixed_product_attribute');
$attribute->delete();
