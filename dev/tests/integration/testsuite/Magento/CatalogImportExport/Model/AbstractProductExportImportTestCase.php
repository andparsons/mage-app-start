<?php
namespace Magento\CatalogImportExport\Model;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\Store;

/**
 * Abstract class for testing product export and import scenarios
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractProductExportImportTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
     * @var string[]
     */
    protected $fixtures;

    /**
     * skipped attributes
     *
     * @var array
     */
    public static $skippedAttributes = [
        'options',
        'created_at',
        'updated_at',
        'category_ids',
        'special_from_date',
        'news_from_date',
        'custom_design_from',
        'updated_in',
        'tax_class_id',
        'description',
        'is_salable', // stock indexation is not performed during import
    ];

    /**
     * @var array
     */
    private static $attributesToRefresh = [
        'tax_class_id',
    ];

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->fileSystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $this->productResource = $this->objectManager->create(
            \Magento\Catalog\Model\ResourceModel\Product::class
        );
        \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType::$commonAttributesCache = [];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->executeFixtures($this->fixtures, true);
    }

    /**
     * Run import/export tests.
     *
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     *
     * @param array $fixtures
     * @param string[] $skus
     * @param string[] $skippedAttributes
     * @return void
     * @dataProvider exportImportDataProvider
     */
    public function testImportExport(array $fixtures, array $skus, array $skippedAttributes = []): void
    {
        $this->fixtures = $fixtures;
        $this->executeFixtures($fixtures);
        $this->modifyData($skus);
        $skippedAttributes = array_merge(self::$skippedAttributes, $skippedAttributes);
        $csvFile = $this->executeExportTest($skus, $skippedAttributes);

        $this->executeImportReplaceTest($skus, $skippedAttributes, false, $csvFile);
        $this->executeImportReplaceTest($skus, $skippedAttributes, true, $csvFile);
        $this->executeImportDeleteTest($skus, $csvFile);
    }

    /**
     * Provide data for import/export.
     *
     * @return array
     */
    abstract public function exportImportDataProvider(): array;

    /**
     * Modify data.
     *
     * @param array $skus
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function modifyData(array $skus): void
    {
    }

    /**
     * Prepare product.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepareProduct(\Magento\Catalog\Model\Product $product): void
    {
    }

    /**
     * Execute export test.
     *
     * @param array $skus
     * @param array $skippedAttributes
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function executeExportTest(array $skus, array $skippedAttributes): string
    {
        $index = 0;
        $ids = [];
        $origProducts = [];
        /** @var \Magento\CatalogInventory\Model\StockRegistryStorage $stockRegistryStorage */
        $stockRegistryStorage = $this->objectManager->get(\Magento\CatalogInventory\Model\StockRegistryStorage::class);
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        while (isset($skus[$index])) {
            $ids[$index] = $this->productResource->getIdBySku($skus[$index]);
            $origProducts[$index] = $productRepository->get($skus[$index], false, Store::DEFAULT_STORE_ID);
            $index++;
        }

        $csvfile = $this->exportProducts();
        $this->importProducts($csvfile, \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND);

        while ($index > 0) {
            $index--;
            $stockRegistryStorage->removeStockItem($ids[$index]);
            $newProduct = $productRepository->get($skus[$index], false, Store::DEFAULT_STORE_ID, true);
            // @todo uncomment or remove after MAGETWO-49806 resolved
            //$this->assertEquals(count($origProductData[$index]), count($newProductData));

            $this->assertEqualsOtherThanSkippedAttributes(
                $origProducts[$index]->getData(),
                $newProduct->getData(),
                $skippedAttributes
            );

            $this->assertEqualsSpecificAttributes($origProducts[$index], $newProduct);
        }

        return $csvfile;
    }

    /**
     * Assert data equals (ignore skipped attributes).
     *
     * @param array $expected
     * @param array $actual
     * @param array $skippedAttributes
     * @return void
     */
    private function assertEqualsOtherThanSkippedAttributes(
        array $expected,
        array $actual,
        array $skippedAttributes
    ): void {
        foreach ($expected as $key => $value) {
            if (is_object($value) || in_array($key, $skippedAttributes)) {
                continue;
            }

            $this->assertEquals(
                $value,
                isset($actual[$key]) ? $actual[$key] : null,
                'Assert value at key - ' . $key . ' failed'
            );
        }
    }

    /**
     * Execute import test with delete behavior.
     *
     * @param array $skus
     * @param string|null $csvFile
     * @return void
     */
    protected function executeImportDeleteTest(array $skus, string $csvFile = null): void
    {
        $csvFile = $csvFile ?? $this->exportProducts();
        $this->importProducts($csvFile, \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE);
        foreach ($skus as $sku) {
            $productId = $this->productResource->getIdBySku($sku);
            $this->assertFalse($productId);
        }
    }

    /**
     * Execute fixtures.
     *
     * @param array $fixtures
     * @param bool $rollback
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function executeFixtures(array $fixtures, bool $rollback = false)
    {
        foreach ($fixtures as $fixture) {
            $fixturePath = $this->resolveFixturePath($fixture, $rollback);
            include $fixturePath;
        }
    }

    /**
     * Resolve fixture path.
     *
     * @param string $fixture
     * @param bool $rollback
     * @return string
     */
    private function resolveFixturePath(string $fixture, bool $rollback = false)
    {
        $fixturePath = $this->fileSystem->getDirectoryRead(DirectoryList::ROOT)
            ->getAbsolutePath('/dev/tests/integration/testsuite/' . $fixture);
        if ($rollback) {
            $fileInfo = pathinfo($fixturePath);
            $extension = '';
            if (isset($fileInfo['extension'])) {
                $extension = '.' . $fileInfo['extension'];
            }
            $fixturePath = $fileInfo['dirname'] . '/' . $fileInfo['filename'] . '_rollback' . $extension;
        }

        return $fixturePath;
    }

    /**
     * Assert that specific attributes equal.
     *
     * @param \Magento\Catalog\Model\Product $expectedProduct
     * @param \Magento\Catalog\Model\Product $actualProduct
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function assertEqualsSpecificAttributes(
        \Magento\Catalog\Model\Product $expectedProduct,
        \Magento\Catalog\Model\Product $actualProduct
    ): void {
        // check custom options
    }

    /**
     * Execute import test with replace behavior.
     *
     * @param string[] $skus
     * @param string[] $skippedAttributes
     * @param bool $usePagination
     * @param string|null $csvfile
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function executeImportReplaceTest(
        $skus,
        $skippedAttributes,
        $usePagination = false,
        string $csvfile = null
    ) {
        $replacedAttributes = [
            'row_id',
            'entity_id',
            'tier_price',
            'media_gallery'
        ];
        $skippedAttributes = array_merge($replacedAttributes, $skippedAttributes);
        $this->cleanAttributesCache();

        $index = 0;
        $ids = [];
        $origProducts = [];
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        while (isset($skus[$index])) {
            $ids[$index] = $this->productResource->getIdBySku($skus[$index]);
            $origProducts[$index] = $productRepository->get($skus[$index], false, Store::DEFAULT_STORE_ID);
            $index++;
        }

        $exportProduct = $this->objectManager->create(\Magento\CatalogImportExport\Model\Export\Product::class);
        if ($usePagination) {
            /** @var \ReflectionProperty $itemsPerPageProperty */
            $itemsPerPageProperty = $this->objectManager->create(\ReflectionProperty::class, [
                'class' => \Magento\CatalogImportExport\Model\Export\Product::class,
                'name' => '_itemsPerPage'
            ]);
            $itemsPerPageProperty->setAccessible(true);
            $itemsPerPageProperty->setValue($exportProduct, 1);
        }

        $csvfile = $csvfile ?? $this->exportProducts($exportProduct);
        $this->importProducts($csvfile, \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE);

        while ($index > 0) {
            $index--;
            $newProduct = $productRepository->get($skus[$index], false, Store::DEFAULT_STORE_ID, true);
            // check original product is deleted
            $productId = $this->productResource->getIdBySku($ids[$index]);
            $this->assertFalse($productId);

            // check new product data
            // @todo uncomment or remove after MAGETWO-49806 resolved
            //$this->assertEquals(count($origProductData[$index]), count($newProductData));

            $origProductData = $origProducts[$index]->getData();
            $newProductData = $newProduct->getData();
            $this->assertEqualsOtherThanSkippedAttributes($origProductData, $newProductData, $skippedAttributes);

            $this->assertEqualsSpecificAttributes($origProducts[$index], $newProduct);

            foreach ($replacedAttributes as $attribute) {
                if (isset($origProductData[$attribute])) {
                    $expected = is_array($origProductData[$attribute]) ?
                        array_filter($origProductData[$attribute]) :
                        $origProductData[$attribute];
                    if (!empty($expected)) {
                        $actual = $newProductData[$attribute] ?? null;
                        $actual = is_array($actual) ? array_filter($actual) : $actual;
                        $this->assertNotEquals($expected, $actual, $attribute . ' is expected to be changed');
                    }
                }
            }
        }
    }

    /**
     * Export products in the system.
     *
     * @param \Magento\CatalogImportExport\Model\Export\Product|null $exportProduct
     * @return string Return exported file name
     */
    private function exportProducts(\Magento\CatalogImportExport\Model\Export\Product $exportProduct = null)
    {
        $csvfile = uniqid('importexport_') . '.csv';

        $exportProduct = $exportProduct ?: $this->objectManager->create(
            \Magento\CatalogImportExport\Model\Export\Product::class
        );
        $exportProduct->setWriter(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\ImportExport\Model\Export\Adapter\Csv::class,
                ['fileSystem' => $this->fileSystem, 'destination' => $csvfile]
            )
        );
        $this->assertNotEmpty($exportProduct->export());

        return $csvfile;
    }

    /**
     * Import products from the given file.
     *
     * @param string $csvfile
     * @param string $behavior
     * @return void
     */
    private function importProducts(string $csvfile, string $behavior): void
    {
        /** @var \Magento\CatalogImportExport\Model\Import\Product $importModel */
        $importModel = $this->objectManager->create(
            \Magento\CatalogImportExport\Model\Import\Product::class
        );
        $directory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => $csvfile,
                'directory' => $directory
            ]
        );

        $appParams = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getBootstrap()
            ->getApplication()
            ->getInitParams()[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS];
        $uploader = $importModel->getUploader();
        $rootDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::ROOT);
        $destDir = $rootDirectory->getRelativePath(
            $appParams[DirectoryList::MEDIA][DirectoryList::PATH] . '/catalog/product'
        );
        $tmpDir = $rootDirectory->getRelativePath(
            $appParams[DirectoryList::MEDIA][DirectoryList::PATH] . '/import'
        );

        $rootDirectory->create($destDir);
        $rootDirectory->create($tmpDir);
        $this->assertTrue($uploader->setDestDir($destDir));
        $this->assertTrue($uploader->setTmpDir($tmpDir));

        $errors = $importModel->setParameters(
            [
                'behavior' => $behavior,
                'entity' => 'catalog_product',
            ]
        )->setSource(
            $source
        )->validateData();
        $errorMessage = $this->extractErrorMessage($errors->getAllErrors());

        $this->assertEmpty(
            $errorMessage,
            'Product import from file ' . $csvfile . ' validation errors: ' . $errorMessage
        );
        $importModel->importData();
        $importErrors = $importModel->getErrorAggregator()->getAllErrors();
        $importErrorMessage = $this->extractErrorMessage($importErrors);
        $this->assertEmpty(
            $importErrorMessage,
            'Product import from file ' . $csvfile . ' errors: ' . $importErrorMessage
        );
    }

    /**
     * Extract error message.
     *
     * @param \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError[] $errors
     * @return string
     */
    private function extractErrorMessage(array $errors): string
    {
        $errorMessage = '';
        foreach ($errors as $error) {
            $errorMessage = "\n" . $error->getErrorMessage();
        }

        return $errorMessage;
    }

    /**
     * Clean import attribute cache.
     *
     * @return void
     */
    private function cleanAttributesCache(): void
    {
        foreach (self::$attributesToRefresh as $attributeCode) {
            $attributeId = Import\Product\Type\AbstractType::$attributeCodeToId[$attributeCode] ?? null;
            if ($attributeId !== null) {
                unset(Import\Product\Type\AbstractType::$commonAttributesCache[$attributeId]);
            }
        }
    }
}
