<?php

namespace Magento\RequisitionList\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ItemsCountTest
 */
class ItemsCountTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\RequisitionList\Ui\Component\Listing\Column\ItemsCount|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemsCount;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListRepositoryMock;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->requisitionListRepositoryMock = $this->getMockBuilder(
            \Magento\RequisitionList\Api\RequisitionListRepositoryInterface::class
        )->disableOriginalConstructor()->getMock();

        $processorMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->never())
            ->method('getProcessor')
            ->willReturn($processorMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->itemsCount = $this->objectManagerHelper->getObject(
            \Magento\RequisitionList\Ui\Component\Listing\Column\ItemsCount::class,
            [
                'context' => $context,
                'requisitionListRepository' => $this->requisitionListRepositoryMock
            ]
        );
    }

    /**
     * Test prepareDataSource
     *
     * @param array $listMap
     * @param array $inputDataSource
     * @param array $expectedDataSource
     * @return void
     *
     * @dataProvider prepareDataSourceProvider
     */
    public function testPrepareDataSource(array $listMap, array $inputDataSource, array $expectedDataSource)
    {
        $this->requisitionListRepositoryMock->expects($this->any())
            ->method('get')
            ->willReturnMap($listMap);

        $this->assertEquals(
            $expectedDataSource,
            $this->itemsCount->prepareDataSource($inputDataSource)
        );
    }

    /**
     * Test prepareDataSource with exception
     *
     * @return void
     */
    public function testPrepareDataSourceException()
    {
        $this->requisitionListRepositoryMock->expects($this->any())
            ->method('get')
            ->willThrowException(new NoSuchEntityException());

        $inputDataSource = $this->buildDataSourceMock([
            [
                'entity_id' => 13
            ]
        ]);
        $expectedDataSource = $this->buildDataSourceMock([
            [
                'entity_id' => 13,
                'items' => 0
            ]
        ]);

        $this->assertEquals(
            $expectedDataSource,
            $this->itemsCount->prepareDataSource($inputDataSource)
        );
    }

    /**
     * Data provider for prepareDataSource
     *
     * @return array
     */
    public function prepareDataSourceProvider()
    {
        return [
            [
                [
                    [1, $this->buildListMock([1, 2, 3])],
                    [2, $this->buildListMock([])]
                ],
                $this->buildDataSourceMock([
                    [
                        'entity_id' => 1
                    ],
                    [
                        'entity_id' => 2
                    ]
                ]),
                $this->buildDataSourceMock([
                    [
                        'entity_id' => 1,
                        'items' => 3
                    ],
                    [
                        'entity_id' => 2,
                        'items' => 0
                    ]
                ])
            ]
        ];
    }

    /**
     * @param array $items
     * @return \Magento\RequisitionList\Api\Data\RequisitionListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function buildListMock(array $items)
    {
        $listMock = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $listMock->expects($this->any())
            ->method('getItems')
            ->willReturn($items);
        return $listMock;
    }

    /**
     * Build data source mock
     *
     * @param array $items
     * @return array
     */
    private function buildDataSourceMock(array $items)
    {
        $dataSourceMock = [
            'data' => [
                'items' => $items
            ]
        ];
        return $dataSourceMock;
    }
}
