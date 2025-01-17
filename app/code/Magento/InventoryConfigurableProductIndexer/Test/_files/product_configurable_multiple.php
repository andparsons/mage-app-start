<?php
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Setup\CategorySetup;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Config;
use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);

/** @var $installer CategorySetup */
$installer = Bootstrap::getObjectManager()->create(CategorySetup::class);

/** @var Website $website */
$website = Bootstrap::getObjectManager()->create(Website::class);
$website->load('us_website', 'code');
$websiteIds = [$website->getId()];

/** @var Config $eavConfig */
$eavConfig = Bootstrap::getObjectManager()->create(Config::class);
$attribute = $eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'test_configurable');

/* Create simple products per each option value*/
/** @var AttributeOptionInterface[] $options */
$options = $attribute->getOptions();

$configurableIds = [1, 2];
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');

foreach ($configurableIds as $configurableId) {
    $attributeValues = [];
    $associatedProductIds = [];
    $productIds = [10, 20, 30];
    array_shift($options); //remove the first option which is empty

    foreach ($options as $option) {
        /** @var $product Product */
        $product = Bootstrap::getObjectManager()->create(Product::class);
        $productId = $configurableId + array_shift($productIds);
        $product->setTypeId(Type::TYPE_SIMPLE)
            ->setId($productId)
            ->setAttributeSetId($attributeSetId)
            ->setWebsiteIds($websiteIds)
            ->setName('Configurable Option' . $option->getLabel())
            ->setSku('simple_' . $productId)
            ->setPrice($productId)
            ->setTestConfigurable($option->getValue())
            ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);

        $product = $productRepository->save($product);

        /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
        $stockItem = Bootstrap::getObjectManager()->create(\Magento\CatalogInventory\Model\Stock\Item::class);
        $stockItem->load($productId, 'product_id');

        if (!$stockItem->getProductId()) {
            $stockItem->setProductId($productId);
        }
        $stockItem->setUseConfigManageStock(1);
        $stockItem->setQty(1000);
        $stockItem->setIsQtyDecimal(0);
        $stockItem->setIsInStock(1);
        $stockItem->save();

        $attributeValues[] = [
            'label' => 'test',
            'attribute_id' => $attribute->getId(),
            'value_index' => $option->getValue(),
        ];
        $associatedProductIds[] = $product->getId();
    }

    /** @var $product Product */
    $product = Bootstrap::getObjectManager()->create(Product::class);

    /** @var Factory $optionsFactory */
    $optionsFactory = Bootstrap::getObjectManager()->create(Factory::class);

    $configurableAttributesData = [
        [
            'attribute_id' => $attribute->getId(),
            'code' => $attribute->getAttributeCode(),
            'label' => $attribute->getStoreLabel(),
            'position' => '0',
            'values' => $attributeValues,
        ],
    ];

    $configurableOptions = $optionsFactory->create($configurableAttributesData);

    $extensionConfigurableAttributes = $product->getExtensionAttributes();
    $extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
    $extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);

    $product->setExtensionAttributes($extensionConfigurableAttributes);

    // Remove any previously created product with the same id.
    /** @var \Magento\Framework\Registry $registry */
    $registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
    $registry->unregister('isSecureArea');
    $registry->register('isSecureArea', true);
    try {
        $productToDelete = $productRepository->getById($configurableId);
        $productRepository->delete($productToDelete);

        /** @var \Magento\Quote\Model\ResourceModel\Quote\Item $itemResource */
        $itemResource = Bootstrap::getObjectManager()->get(\Magento\Quote\Model\ResourceModel\Quote\Item::class);
        $itemResource->getConnection()->delete(
            $itemResource->getMainTable(),
            'product_id = ' . $productToDelete->getId()
        );
    } catch (\Exception $e) {
        // Nothing to remove
    }
    $registry->unregister('isSecureArea');
    $registry->register('isSecureArea', false);

    $product->setTypeId(Configurable::TYPE_CODE)
        ->setId($configurableId)
        ->setAttributeSetId($attributeSetId)
        ->setWebsiteIds($websiteIds)
        ->setName('Configurable Product ' . $configurableId)
        ->setSku('configurable_' . $configurableId)
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);

    $productRepository->save($product);

    /** @var \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement */
    $categoryLinkManagement = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
        ->create(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);

    $categoryLinkManagement->assignProductToCategories(
        $product->getSku(),
        [2]
    );
}
