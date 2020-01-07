<?php

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Setup\CategorySetup;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Msrp\Model\Product\Attribute\Source\Type;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../../CatalogDataExporter/Test/Integration/_files/setup_stores.php';
require __DIR__ . '/../../../../CatalogDataExporter/Test/Integration/_files/setup_categories.php';
require __DIR__ . '/setup_configurable_attribute.php';

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var Magento\Catalog\Api\CategoryLinkManagementInterface $linkManagement */
$categoryLinkManagement = $objectManager->create(CategoryLinkManagementInterface::class);

/** @var CategorySetup $installer */
$installer = $objectManager->create(CategorySetup::class);

/* Create simple products per each option value*/
/** @var Magento\Eav\Api\Data\AttributeOptionInterface[] $options */
$options = $attribute->getOptions();

$attributeValues = [];
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');

/* Create simple products per each option value*/
/** @var AttributeOptionInterface[] $options */
$options = $attribute->getOptions();
$attributeValues = [];
$associatedProductIds = [];
$productIds = [50, 60, 70];
array_shift($options); //remove the first option which is empty

foreach ($options as $option) {
    /** @var Product $product */
    $product = $objectManager->create(Product::class);
    $productId = array_shift($productIds);
    $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
        ->setId($productId)
        ->setAttributeSetId($attributeSetId)
        ->setName('Configurable Option' . $option->getLabel())
        ->setSku('simple_option_' . $productId)
        ->setTaxClassId('none')
        ->setDescription('description')
        ->setShortDescription('short description')
        ->setPrice($productId)
        ->setWeight(1)
        ->setMetaTitle('meta title')
        ->setMetaKeyword('meta keyword')
        ->setMetaDescription('meta description')
        ->setTestConfigurable($option->getValue())
        ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
        ->setStatus(Status::STATUS_ENABLED)
        ->setWebsiteIds([1])
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
        ->setSpecialPrice('5.99')
        ->setImage('/m/a/magento_image.jpg')
        ->setSmallImage('/m/a/magento_small_image.jpg')
        ->setThumbnail('/m/a/magento_thumbnail.jpg')
        ->save();

    $attributeValues[] = [
        'label' => 'test',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getValue(),
    ];
    $associatedProductIds[] = $product->getId();
}

$configurableAttributesData = [
    [
        'attribute_id' => $attribute->getId(),
        'code' => $attribute->getAttributeCode(),
        'label' => $attribute->getStoreLabel(),
        'position' => '0',
        'values' => $attributeValues
    ]
];

/** @var Product $product */
$product = $objectManager->create(Product::class);
/** @var Factory $optionsFactory */
$optionsFactory = $objectManager->create(Factory::class);
$configurableOptions = $optionsFactory->create($configurableAttributesData);
$extensionConfigurableAttributes = $product->getExtensionAttributes();
$extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
$extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);

$product->setExtensionAttributes($extensionConfigurableAttributes);
$product->isObjectNew(true);
$product->setTypeId(Configurable::TYPE_CODE)
    ->setId(40)
    ->setAttributeSetId($attributeSetId)
    ->setName('Configurable Product1')
    ->setSku('configurable1')
    ->setTaxClassId('none')
    ->setDescription('description')
    ->setShortDescription('short description')
    ->setOptionsContainer('container1')
    ->setMsrpDisplayActualPriceType(Type::TYPE_IN_CART)
    ->setPrice(10)
    ->setWeight(1)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setSpecialPrice('5.99')
    ->setImage('/m/a/magento_image.jpg')
    ->setSmallImage('/m/a/magento_small_image.jpg')
    ->setThumbnail('/m/a/magento_thumbnail.jpg')
    ->save();
$categoryLinkManagement->assignProductToCategories($product->getSku(), [100, 200]);
