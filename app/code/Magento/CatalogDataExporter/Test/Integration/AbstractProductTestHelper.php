<?php
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Test\Integration;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Indexer\Model\Indexer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\TaxClass\Source\Product as TaxClassSource;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Abstract Class AbstractProductTestHelper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractProductTestHelper extends \PHPUnit\Framework\TestCase
{
    /**
     * Test Constants
     */
    const WEBSITE_CODE = 'base';
    const STORE_CODE = 'main_website_store';
    const CATALOG_DATA_EXPORTER = 'catalog_data_exporter_products';

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var String
     */
    protected $connection;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var Indexer
     */
    protected $indexer;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * @var CategoryHelper
     */
    protected $categoryHelper;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var TaxClassSource
     */
    protected $taxClassSource;

    /**
     * Setup tests
     */
    protected function setUp()
    {
        $this->resource = Bootstrap::getObjectManager()->create(ResourceConnection::class);
        $this->connection = $this->resource->getConnection();
        $this->indexer = Bootstrap::getObjectManager()->create(Indexer::class);

        $this->categoryRepository = Bootstrap::getObjectManager()->create(CategoryRepositoryInterface::class);
        $this->categoryHelper = Bootstrap::getObjectManager()->create(CategoryHelper::class);

        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->productHelper = Bootstrap::getObjectManager()->create(ProductHelper::class);
        $this->storeManager = Bootstrap::getObjectManager()->create(StoreManagerInterface::class);
        $this->taxClassSource = Bootstrap::getObjectManager()->create(TaxClassSource::class);

        $this->jsonSerializer = Bootstrap::getObjectManager()->create(Json::class);
    }

    /**
     * Run the indexer to extract product data
     *
     * @return void
     * @throws \Throwable
     */
    protected function runIndexer() : void
    {
        $this->indexer->load(self::CATALOG_DATA_EXPORTER);
        $this->indexer->reindexAll();
    }

    /**
     * Get the extracted product data stored in the catalog extract table
     *
     * @param string $sku
     * @param string $storeViewCode
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    protected function getExtractedProduct(string $sku, string $storeViewCode) : array
    {
        $query = $this->connection->select()
            ->from(['ex' => self::CATALOG_DATA_EXPORTER])
            ->where('ex.sku = ?', $sku)
            ->where('ex.store_view_code = ?', $storeViewCode);
        $cursor = $this->connection->query($query);
        $data = [];
        while ($row = $cursor->fetch()) {
            $data[$row['sku']]['sku'] = $row['sku'];
            $data[$row['sku']]['store_view_code'] = $row['store_view_code'];
            $data[$row['sku']]['modified_at'] = $row['modified_at'];
            $data[$row['sku']]['is_deleted'] = $row['is_deleted'];
            $data[$row['sku']]['feedData'] = $this->jsonSerializer->unserialize($row['feed_data']);
        }
        return $data[$sku];
    }

    /**
     * Get the pricing data for product and website
     *
     * @param ProductInterface $product
     * @return array
     * @throws LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    protected function getPricingData(ProductInterface $product) : array
    {
        $query = $this->connection->select()
            ->from(['p' => 'catalog_product_index_price'])
            ->where('p.entity_id = ?', $product->getId())
            ->where('p.customer_group_id = 0')
            ->where('p.website_id = ?', $this->storeManager->getWebsite()->getId());
        $cursor = $this->connection->query($query);
        $data = [];
        while ($row = $cursor->fetch()) {
            $data['price'] = $row['price'];
            $data['final_price'] = $row['final_price'];
            $data['min_price'] = $row['min_price'];
            $data['max_price'] = $row['max_price'];
        }
        return $data;
    }

    /**
     * Validate pricing data in extracted product data
     *
     * @param ProductInterface $product
     * @param array $extractedProduct
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    protected function validatePricingData(ProductInterface $product, array $extractedProduct) : void
    {
        $pricingData = $this->getPricingData($product);
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();

        $this->assertEquals($currencyCode, $extractedProduct['feedData']['currency']);
        if ($product->getStatus() == 1) {
            $extractedPricingData = $extractedProduct['feedData']['prices'];
            $this->assertEquals($pricingData['price'], $extractedPricingData['minimumPrice']['regularPrice']);
            $this->assertEquals($pricingData['final_price'], $extractedPricingData['minimumPrice']['finalPrice']);
            $this->assertEquals($pricingData['max_price'], $extractedPricingData['maximumPrice']['regularPrice']);
            $this->assertEquals($pricingData['max_price'], $extractedPricingData['maximumPrice']['finalPrice']);
        } else {
            $this->assertEquals(null, $extractedProduct['feedData']['prices']);
        }
    }

    /**
     * Validate category data in extracted product data
     *
     * @param ProductInterface $product
     * @param array $extractedProduct
     * @return void
     * @throws NoSuchEntityException
     */
    protected function validateCategoryData(ProductInterface $product, array $extractedProduct) : void
    {
        $storeId = $this->storeManager->getStore()->getId();
        $categories = [];
        foreach ($product->getCategoryIds() as $categoryId) {
            $category = $this->categoryRepository->get($categoryId, $storeId);
            $parentId = $category->getParentId();
            $path = $category->getUrlKey();
            while ($parentId) {
                $parent = $this->categoryRepository->get($parentId, $storeId);
                $parentId = $parent->getParentId();
                $urlKey = $parent->getUrlKey();
                if ($urlKey) {
                    $path = $urlKey . '/' . $path;
                }
            }
            $categories[] = $path;
        }

        $this->assertEquals($categories, $extractedProduct['feedData']['categories']);
    }

    /**
     * Validate base product data in extracted product data
     *
     * @param ProductInterface $product
     * @param array $extract
     * @param string $storeViewCode
     * @return void
     * @throws LocalizedException
     */
    protected function validateBaseProductData(ProductInterface $product, array $extract, string $storeViewCode) : void
    {
        $enabled = $product->getStatus() == 1 ? 'Enabled' : 'Disabled';
        $visibility = Visibility::getOptionText($product->getVisibility());

        $this->assertEquals($product->getSku(), $extract['sku']);
        $this->assertEquals($product->getSku(), $extract['feedData']['sku']);
        $this->assertEquals($product->getId(), $extract['feedData']['productId']);
        $this->assertEquals(self::WEBSITE_CODE, $extract['feedData']['websiteCode']);
        $this->assertEquals(self::STORE_CODE, $extract['feedData']['storeCode']);
        $this->assertEquals($storeViewCode, $extract['feedData']['storeViewCode']);
        $this->assertEquals($product->getName(), $extract['feedData']['name']);
        $this->assertEquals($enabled, $extract['feedData']['status']);
        $this->assertEquals($product->getId(), $extract['feedData']['productId']);
        $this->assertEquals($product->getTypeId(), $extract['feedData']['type']);
        $this->assertEquals($product->getUrlKey(), $extract['feedData']['urlKey']);
        $this->assertEquals($product->getCreatedAt(), $extract['feedData']['createdAt']);
        $this->assertEquals($product->getUpdatedAt(), $extract['feedData']['updatedAt']);
        $this->assertEquals($product->getWeight(), $extract['feedData']['weight']);
        $this->assertEquals($product->getDescription(), $extract['feedData']['description']);
        $this->assertEquals($product->getMetaDescription(), $extract['feedData']['metaDescription']);
        $this->assertEquals($product->getMetaKeyword(), $extract['feedData']['metaKeyword']);
        $this->assertEquals($product->getMetaTitle(), $extract['feedData']['metaTitle']);
        $this->assertEquals($product->getTaxClassId(), $extract['feedData']['taxClassId']);
        $this->assertEquals($visibility, $extract['feedData']['visibility']);
    }

    /**
     * Validate product image URLs in extracted product data
     *
     * @param ProductInterface $product
     * @param array $extractedProduct
     * @return void
     */
    protected function validateImageUrls(ProductInterface $product, array $extractedProduct) : void
    {
        $regex = '/cache\/(.*?)\/(.*?)/';
        $extractedImageUrl = preg_replace($regex, '', $extractedProduct['feedData']['image']['url']);
        $extractedSmallImageUrl = preg_replace($regex, '', $extractedProduct['feedData']['smallImage']['url']);
        $extractedThumbnailUrl = preg_replace($regex, '', $extractedProduct['feedData']['thumbnail']['url']);

        $this->assertEquals($this->productHelper->getImageUrl($product), $extractedImageUrl);
        $this->assertEquals($this->productHelper->getSmallImageUrl($product), $extractedSmallImageUrl);
        $this->assertEquals($this->productHelper->getThumbnailUrl($product), $extractedThumbnailUrl);
    }

    /**
     * Validate product attributes in extracted product data
     *
     * @param ProductInterface $product
     * @param array $extractedProduct
     * @return void
     */
    protected function validateAttributeData(ProductInterface $product, array $extractedProduct) : void
    {
        $customLabel = $product->getCustomAttribute('custom_label');
        $customDescription = $product->getCustomAttribute('custom_description');
        $customSelect = $product->getCustomAttribute('custom_select');

        $attributes = null;
        if ($customLabel) {
            $attributes[] = [
                'attributeCode' => $customLabel->getAttributeCode(),
                'value' => [$customLabel->getValue()]
            ];
        }
        if ($customDescription) {
            $attributes[] = [
                'attributeCode' => $customDescription->getAttributeCode(),
                'value' => [$customDescription->getValue()]
            ];
        }
        if ($customSelect) {
            $attributes[] = [
                'attributeCode' => $customSelect->getAttributeCode(),
                'value' => [$product->getAttributeText('custom_select')]
            ];
        }

        $this->assertEquals($attributes, $extractedProduct['feedData']['attributes']);
    }
}
