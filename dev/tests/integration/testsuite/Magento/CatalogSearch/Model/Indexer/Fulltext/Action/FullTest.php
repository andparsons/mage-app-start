<?php
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\CatalogSearch\Model\ResourceModel\Engine;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\Product;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class for testing fulltext index rebuild
 */
class FullTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full
     */
    protected $actionFull;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->actionFull = Bootstrap::getObjectManager()->create(
            \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full::class
        );
    }

    /**
     * Testing fulltext index rebuild
     *
     * @magentoDataFixture Magento/CatalogSearch/_files/products_for_index.php
     * @magentoDataFixture Magento/CatalogSearch/_files/product_configurable_not_available.php
     * @magentoDataFixture Magento/Framework/Search/_files/product_configurable.php
     */
    public function testGetIndexData()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $allowedStatuses = Bootstrap::getObjectManager()->get(Status::class)->getVisibleStatusIds();
        $allowedVisibility = Bootstrap::getObjectManager()->get(Engine::class)->getAllowedVisibility();
        $result = iterator_to_array($this->actionFull->rebuildStoreIndex(Store::DISTRO_STORE_ID));
        $this->assertNotEmpty($result);

        $productsIds = array_keys($result);
        foreach ($productsIds as $productId) {
            $product = $productRepository->getById($productId);
            $this->assertContains($product->getVisibility(), $allowedVisibility);
            $this->assertContains($product->getStatus(), $allowedStatuses);
        }

        $expectedData = $this->getExpectedIndexData();
        foreach ($expectedData as $sku => $expectedIndexData) {
            $product = $productRepository->get($sku);
            $this->assertEquals($expectedIndexData, $result[$product->getId()]);
        }
    }

    /**
     * Prepare and return expected index data
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getExpectedIndexData()
    {
        /** @var ProductAttributeRepositoryInterface $attributeRepository */
        $attributeRepository = Bootstrap::getObjectManager()->get(ProductAttributeRepositoryInterface::class);
        $skuId = $attributeRepository->get(ProductInterface::SKU)->getAttributeId();
        $nameId = $attributeRepository->get(ProductInterface::NAME)->getAttributeId();
        /** @see dev/tests/integration/testsuite/Magento/Framework/Search/_files/configurable_attribute.php */
        $configurableId = $attributeRepository->get('test_configurable')->getAttributeId();
        $statusId = $attributeRepository->get(ProductInterface::STATUS)->getAttributeId();
        $taxClassId = $attributeRepository
            ->get(\Magento\Customer\Api\Data\GroupInterface::TAX_CLASS_ID)
            ->getAttributeId();
        return [
            'configurable' => [
                $skuId => 'configurable',
                $configurableId => 'Option 2',
                $nameId => 'Configurable Product | Configurable OptionOption 2',
                $taxClassId => 'Taxable Goods | Taxable Goods',
                $statusId => 'Enabled | Enabled'
            ],
            'index_enabled' => [
                $skuId => 'index_enabled',
                $nameId => 'index enabled',
                $taxClassId => 'Taxable Goods',
                $statusId => 'Enabled'
            ],
            'index_visible_search' => [
                $skuId => 'index_visible_search',
                $nameId => 'index visible search',
                $taxClassId => 'Taxable Goods',
                $statusId => 'Enabled'
            ],
            'index_visible_category' => [
                $skuId => 'index_visible_category',
                $nameId => 'index visible category',
                $taxClassId => 'Taxable Goods',
                $statusId => 'Enabled'
            ],
            'index_visible_both' => [
                $skuId => 'index_visible_both',
                $nameId => 'index visible both',
                $taxClassId => 'Taxable Goods',
                $statusId => 'Enabled'
            ]
        ];
    }

    /**
     * Testing fulltext index rebuild with configurations
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testRebuildStoreIndexConfigurable()
    {
        $storeId = 1;

        $simpleProductId = $this->getIdBySku('simple_10');
        $configProductId = $this->getIdBySku('configurable');

        $expected = [
            $simpleProductId,
            $configProductId
        ];
        $storeIndexDataSimple = $this->actionFull->rebuildStoreIndex($storeId, [$simpleProductId]);
        $storeIndexDataExpected = $this->actionFull->rebuildStoreIndex($storeId, $expected);

        $this->assertEquals($storeIndexDataSimple, $storeIndexDataExpected);
    }

    /**
     * Get product Id by its SKU
     *
     * @param string $sku
     * @return int
     */
    private function getIdBySku($sku)
    {
        /** @var Product $product */
        $product = Bootstrap::getObjectManager()->get(Product::class);

        return $product->getIdBySku($sku);
    }
}
