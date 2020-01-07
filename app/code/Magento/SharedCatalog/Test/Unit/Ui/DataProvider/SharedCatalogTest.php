<?php

namespace Magento\SharedCatalog\Test\Unit\Ui\DataProvider;

use \Magento\SharedCatalog\Ui\DataProvider\Collection\SharedCatalogFactory;
use \Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;

/**
 * Test for UI DataProvider\SharedCatalog.
 */
class SharedCatalogTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SharedCatalogFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var FilterPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterPool;

    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\SharedCatalog|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->filterPool = $this
            ->getMockBuilder(\Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool::class)
            ->disableOriginalConstructor()->getMock();

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * Test for getCatalogDetailsData().
     *
     * @return void
     */
    public function testGetCatalogDetailsData()
    {
        $name = 'sample Name';
        $description = 'sample description';
        $customerGroupId = '123';
        $type = 'sample type';
        $taxClassId = 234;
        $createdAt = 'sample created at';
        $createdBy = 'sample created by';
        $sharedCatalog = $this->getMockBuilder(\Magento\Framework\Api\Search\DocumentInterface::class)
            ->setMethods([
                'getName', 'getDescription', 'getCustomerGroupId', 'getType', 'getTaxClassId',
                'getCreatedAt', 'getCreatedBy'
            ])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $sharedCatalog->expects($this->once())->method('getName')->willReturn($name);
        $sharedCatalog->expects($this->once())->method('getDescription')->willReturn($description);
        $sharedCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $sharedCatalog->expects($this->once())->method('getType')->willReturn($type);
        $sharedCatalog->expects($this->once())->method('getTaxClassId')->willReturn($taxClassId);
        $sharedCatalog->expects($this->once())->method('getCreatedAt')->willReturn($createdAt);
        $sharedCatalog->expects($this->once())->method('getCreatedBy')->willReturn($createdBy);
        $this->collectionFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Ui\DataProvider\Collection\SharedCatalogFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $this->sharedCatalogMock = $this->objectManager->getObject(
            \Magento\SharedCatalog\Ui\DataProvider\SharedCatalog::class,
            [
                'collectionFactory' => $this->collectionFactory,
                'filterPool' => $this->filterPool,
            ]
        );
        $this->sharedCatalogMock->getCatalogDetailsData($sharedCatalog);
    }

    /**
     * Data provider for getData().
     *
     * @return array
     */
    public function testGetDataDataProvider()
    {
        return [
            [true],
            [null],
        ];
    }

    /**
     * Test getData().
     *
     * @param array $loadedData
     * @dataProvider testGetDataDataProvider
     * @return void
     */
    public function testGetData($loadedData)
    {
        if (isset($loadedData)) {
            $this->checkLoadedDataIsSetCase();
        } else {
            $this->checkLoadedDataNotSetCase();
        }
    }

    /**
     * Case for test getData(): loaded data is set.
     *
     * @return void
     */
    private function checkLoadedDataIsSetCase()
    {
        $data = 'sample data';
        $this->collectionFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Ui\DataProvider\Collection\SharedCatalogFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $this->sharedCatalogMock = $this->objectManager->getObject(
            \Magento\SharedCatalog\Ui\DataProvider\SharedCatalog::class,
            [
                '',
                '',
                '',
                'collectionFactory' => $this->collectionFactory,
                'filterPool' => $this->filterPool,
                'meta' => [],
                'data' => [],
            ]
        );

        $reflection = new \ReflectionClass($this->sharedCatalogMock);
        $reflectionProperty = $reflection->getProperty('loadedData');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->sharedCatalogMock, $data);
        $actualResult = $this->sharedCatalogMock->getData();
        $this->assertEquals($data, $actualResult);
    }

    /**
     * Case for test getData(): loaded data not set.
     *
     * @return void
     */
    private function checkLoadedDataNotSetCase()
    {
        $id = 1234;
        $expectedResult = [
            $id => [
                'catalog_details' => [
                    'name' => null,
                    'description' => null,
                    'customer_group_id' => null,
                    'type' => null,
                    'tax_class_id' => null,
                    'created_at' => null,
                    'created_by' => null,
                    'tax_class_id' => null
                ],
                'shared_catalog_id' => $id
            ]
        ];
        $sharedCatalog = $this->getMockBuilder(\Magento\Framework\Api\Search\DocumentInterface::class)
            ->setMethods([
                'getName', 'getDescription', 'getCustomerGroupId', 'getType', 'getTaxClassId',
                'getCreatedAt', 'getCreatedBy'
            ])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        //number inside the exactly() clause must be 2*(number of elements in array $items)
        $sharedCatalog->expects($this->exactly(2))->method('getId')->willReturn($id);

        $items = [
            $sharedCatalog,
        ];
        $collection = $this
            ->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::class)
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $collection->expects($this->once())->method('getItems')->willReturn($items);

        $this->collectionFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Ui\DataProvider\Collection\SharedCatalogFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);

        $modifiers = $this->getMockBuilder(\Magento\Ui\DataProvider\Modifier\PoolInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $modifier = $this->getMockBuilder(\Magento\Ui\DataProvider\Modifier\ModifierInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $modifier->expects($this->once())->method('modifyData')->willReturnArgument(0);
        $modifiers->expects($this->once())->method('getModifiersInstances')->willReturn([$modifier]);

        $this->sharedCatalogMock = $this->objectManager->getObject(
            \Magento\SharedCatalog\Ui\DataProvider\SharedCatalog::class,
            [
                'collectionFactory' => $this->collectionFactory,
                'filterPool' => $this->filterPool,
                'modifiers' => $modifiers,
            ]
        );
        $actualResult = $this->sharedCatalogMock->getData();
        $this->assertEquals($expectedResult, $actualResult);
    }
}
