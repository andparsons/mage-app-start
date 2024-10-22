<?php
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Categories;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Framework\AuthorizationInterface;

/**
 * Class CategoriesTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoriesTest extends AbstractModifierTest
{
    /**
     * @var CategoryCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryCollectionFactoryMock;

    /**
     * @var DbHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dbHelperMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var CategoryCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryCollectionMock;

    /**
     * @var AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationMock;

    protected function setUp()
    {
        parent::setUp();
        $this->categoryCollectionFactoryMock = $this->getMockBuilder(CategoryCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbHelperMock = $this->getMockBuilder(DbHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryCollectionMock = $this->getMockBuilder(CategoryCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authorizationMock = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->categoryCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->categoryCollectionMock);
        $this->categoryCollectionMock->expects($this->any())
            ->method('addAttributeToSelect')
            ->willReturnSelf();
        $this->categoryCollectionMock->expects($this->any())
            ->method('addAttributeToFilter')
            ->willReturnSelf();
        $this->categoryCollectionMock->expects($this->any())
            ->method('setStoreId')
            ->willReturnSelf();
        $this->categoryCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(
            Categories::class,
            [
                'locator' => $this->locatorMock,
                'categoryCollectionFactory' => $this->categoryCollectionFactoryMock,
                'arrayManager' => $this->arrayManagerMock,
                'authorization' => $this->authorizationMock
            ]
        );
    }

    public function testModifyData()
    {
        $this->assertSame([], $this->getModel()->modifyData([]));
    }

    public function testModifyMeta()
    {
        $groupCode = 'test_group_code';
        $meta = [
            $groupCode => [
                'children' => [
                    'category_ids' => [
                        'sortOrder' => 10,
                    ],
                ],
            ],
        ];

        $this->assertArrayHasKey($groupCode, $this->getModel()->modifyMeta($meta));
    }

    /**
     * @param bool $locked
     * @dataProvider modifyMetaLockedDataProvider
     */
    public function testModifyMetaLocked($locked)
    {
        $groupCode = 'test_group_code';
        $meta = [
            $groupCode => [
                'children' => [
                    'category_ids' => [
                        'sortOrder' => 10,
                    ],
                ],
            ],
        ];
        $this->authorizationMock->expects($this->exactly(2))
            ->method('isAllowed')
            ->willReturn(true);
        $this->arrayManagerMock->expects($this->any())
            ->method('findPath')
            ->willReturn('path');

        $this->productMock->expects($this->any())
            ->method('isLockedAttribute')
            ->willReturn($locked);

        $this->arrayManagerMock->expects($this->any())
            ->method('merge')
            ->willReturnArgument(2);

        $modifyMeta = $this->createModel()->modifyMeta($meta);
        $this->assertEquals($locked, $modifyMeta['arguments']['data']['config']['disabled']);
    }

    /**
     * @return array
     */
    public function modifyMetaLockedDataProvider()
    {
        return [[true], [false]];
    }
}
