<?php
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CategoryProcessorTest extends \PHPUnit\Framework\TestCase
{
    const PARENT_CATEGORY_ID = 1;

    const CHILD_CATEGORY_ID = 2;

    const CHILD_CATEGORY_NAME = 'Child';

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryProcessor;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $childCategory;

    /**
     * \Magento\Catalog\Model\Category
     */
    private $parentCategory;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->childCategory = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->childCategory->method('getId')->will($this->returnValue(self::CHILD_CATEGORY_ID));
        $this->childCategory->method('getName')->will($this->returnValue(self::CHILD_CATEGORY_NAME));
        $this->childCategory->method('getPath')->will($this->returnValue(
            self::PARENT_CATEGORY_ID . CategoryProcessor::DELIMITER_CATEGORY
            . self::CHILD_CATEGORY_ID
        ));

        $this->parentCategory = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->parentCategory->method('getId')->will($this->returnValue(self::PARENT_CATEGORY_ID));
        $this->parentCategory->method('getName')->will($this->returnValue('Parent'));
        $this->parentCategory->method('getPath')->will($this->returnValue(self::PARENT_CATEGORY_ID));

        $categoryCollection =
            $this->objectManagerHelper->getCollectionMock(
                \Magento\Catalog\Model\ResourceModel\Category\Collection::class,
                [
                    self::PARENT_CATEGORY_ID => $this->parentCategory,
                    self::CHILD_CATEGORY_ID => $this->childCategory,
                ]
            );
        $map = [
            [self::PARENT_CATEGORY_ID, $this->parentCategory],
            [self::CHILD_CATEGORY_ID, $this->childCategory],
        ];
        $categoryCollection->expects($this->any())
            ->method('getItemById')
            ->will($this->returnValueMap($map));
        $categoryCollection->expects($this->exactly(3))
            ->method('addAttributeToSelect')
            ->withConsecutive(
                ['name'],
                ['url_key'],
                ['url_path']
            )
            ->will($this->returnSelf());

        $categoryColFactory = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory::class,
            ['create']
        );

        $categoryColFactory->method('create')->will($this->returnValue($categoryCollection));

        $categoryFactory = $this->createPartialMock(\Magento\Catalog\Model\CategoryFactory::class, ['create']);

        $categoryFactory->method('create')->will($this->returnValue($this->childCategory));

        $this->categoryProcessor =
            new \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor(
                $categoryColFactory,
                $categoryFactory
            );
    }

    public function testUpsertCategories()
    {
        $categoriesSeparator = ',';
        $categoryIds = $this->categoryProcessor->upsertCategories(self::CHILD_CATEGORY_NAME, $categoriesSeparator);
        $this->assertArrayHasKey(self::CHILD_CATEGORY_ID, array_flip($categoryIds));
    }

    /**
     * Tests case when newly created category save throws exception.
     */
    public function testUpsertCategoriesWithAlreadyExistsException()
    {
        $exception = new \Magento\Framework\Exception\AlreadyExistsException();
        $categoriesSeparator = '/';
        $categoryName = 'Exception Category';
        $this->childCategory->method('save')->willThrowException($exception);
        $this->categoryProcessor->upsertCategories($categoryName, $categoriesSeparator);
        $this->assertNotEmpty($this->categoryProcessor->getFailedCategories());
    }

    public function testClearFailedCategories()
    {
        $dummyFailedCategory = [
            [
                'category' => 'dummy category',
                'exception' => 'dummy exception',
            ]
        ];

        $this->setPropertyValue($this->categoryProcessor, 'failedCategories', $dummyFailedCategory);
        $this->assertCount(count($dummyFailedCategory), $this->categoryProcessor->getFailedCategories());

        $this->categoryProcessor->clearFailedCategories();
        $this->assertEmpty($this->categoryProcessor->getFailedCategories());
    }

    /**
     * Cover getCategoryById().
     *
     * @dataProvider getCategoryByIdDataProvider
     */
    public function testGetCategoryById($categoriesCache, $expectedResult)
    {
        $categoryId = 'category_id';
        $this->setPropertyValue($this->categoryProcessor, 'categoriesCache', $categoriesCache);

        $actualResult = $this->categoryProcessor->getCategoryById($categoryId);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function getCategoryByIdDataProvider()
    {
        return [
            [
                '$categoriesCache' => [
                    'category_id' => 'category_id value',
                ],
                '$expectedResult' => 'category_id value',
            ],
            [
                '$categoriesCache' => [],
                '$expectedResult' => null,
            ],
        ];
    }

    /**
     * Set property for an object.
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     */
    protected function setPropertyValue(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
        return $object;
    }
}
