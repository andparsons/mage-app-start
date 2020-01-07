<?php
namespace Magento\QuickOrder\Test\Unit\Model\Product\Suggest;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\QuickOrder\Model\ResourceModel\Product\Suggest;

/**
 * Unit tests for Data Provider for Quick Order auto-suggest object.
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\QuickOrder\Model\Product\Suggest\DataProvider
     */
    private $dataProvider;

    /**
     * @var \Magento\QuickOrder\Model\FulltextSearch|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fulltextSearchMock;

    /**
     * @var \Magento\QuickOrder\Model\ResourceModel\Product\Suggest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $suggestResourceMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * Result limit parameter for DataProvider constructor.
     *
     * @var int
     */
    private $resultLimit;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->resultLimit = 2;

        $this->fulltextSearchMock = $this->getMockBuilder(\Magento\QuickOrder\Model\FulltextSearch::class)
            ->disableOriginalConstructor()
            ->setMethods(['search'])
            ->getMock();

        $this->suggestResourceMock = $this->getMockBuilder(Suggest::class)
            ->disableOriginalConstructor()
            ->setMethods(['prepareProductCollection'])
            ->getMock();

        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->dataProvider = $this->objectManagerHelper->getObject(
            \Magento\QuickOrder\Model\Product\Suggest\DataProvider::class,
            [
                'collectionFactory' => $this->collectionFactoryMock,
                'fulltextSearch' => $this->fulltextSearchMock,
                'suggestResource' => $this->suggestResourceMock,
                'resultLimit' => $this->resultLimit
            ]
        );
    }

    /**
     * Test for getItems() method.
     *
     * @param array $items
     * @param array $expectedResult
     * @return void
     *
     * @dataProvider getItemsDataProvider
     */
    public function testGetItems(array $items, array $expectedResult)
    {
        $query = 'sku-2';
        $documentMock = $this->getMockBuilder(\Magento\Framework\Api\Search\DocumentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fulltextSearchMock->expects($this->atLeastOnce())->method('search')->willReturn([$documentMock]);
        $collectionMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'load',
                'getItems'
            ])
            ->getMock();
        $this->collectionFactoryMock->expects($this->atLeastOnce())->method('create')->willReturn($collectionMock);
        $this->suggestResourceMock->expects($this->atLeastOnce())->method('prepareProductCollection')
            ->with($collectionMock, [$documentMock], $this->resultLimit, $query)
            ->willReturn($collectionMock);
        $collectionMock->expects($this->atLeastOnce())->method('load')->willReturnSelf();
        $collectionMock->expects($this->atLeastOnce())->method('getItems')->willReturn($items);

        $this->assertEquals($expectedResult, $this->dataProvider->getItems($query));
    }

    /**
     * Data provider for getItems.
     *
     * @return array
     */
    public function getItemsDataProvider()
    {
        $item1 = $this->createProductMock('sku-1');
        $item2 = $this->createProductMock('sku-2');

        return [
            [
                [
                    $item1,
                    $item2
                ],
                [
                    ['id' => 'sku-2', 'value' => 'sku-2', 'labelSku' => 'sku-2', 'labelProductName' => 'sku-2'],
                    ['id' => 'sku-1', 'value' => 'sku-1', 'labelSku' => 'sku-1', 'labelProductName' => 'sku-1']
                ]
            ]
        ];
    }

    /**
     * Create product mock.
     *
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createProductMock($sku)
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productMock->expects($this->any())
            ->method('getSku')
            ->willReturn($sku);
        $productMock->expects($this->any())
            ->method('getName')
            ->willReturn($sku);
        return $productMock;
    }
}
