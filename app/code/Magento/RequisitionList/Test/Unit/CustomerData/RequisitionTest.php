<?php

namespace Magento\RequisitionList\Test\Unit\CustomerData;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class RequisitionTest
 */
class RequisitionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\RequisitionList\CustomerData\Requisition|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisition;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListRepositoryMock;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContextMock;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var \Magento\RequisitionList\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleConfigMock;

    /**
     * @var \Magento\Framework\Api\SortOrderBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sortOrderBuilderMock;

    /**
     * @var \Magento\Framework\Api\SearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsMock;

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
        $this->userContextMock = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->moduleConfigMock = $this->getMockBuilder(\Magento\RequisitionList\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sortOrderBuilderMock = $this->getMockBuilder(\Magento\Framework\Api\SortOrderBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $searchCriteriaMock = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('addFilter')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('addSortOrder')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $sortOrder = $this->getMockBuilder(\Magento\Framework\Api\SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sortOrderBuilderMock->expects($this->any())->method('setField')->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->any())->method('setAscendingDirection')->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->any())->method('create')->willReturn($sortOrder);

        $this->searchResultsMock = $this->getMockBuilder(\Magento\Framework\Api\SearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListRepositoryMock->expects($this->any())
            ->method('getList')
            ->willReturn($this->searchResultsMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->requisition = $this->objectManagerHelper->getObject(
            \Magento\RequisitionList\CustomerData\Requisition::class,
            [
                'requisitionListRepository' => $this->requisitionListRepositoryMock,
                'userContext' => $this->userContextMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'moduleConfig' => $this->moduleConfigMock,
                'sortOrderBuilder' => $this->sortOrderBuilderMock
            ]
        );
    }

    /**
     * @param int $customerId
     * @param array $items
     * @param int $maxListCount
     * @param mixed $expectedCustomerData
     * @return void
     *
     * @dataProvider getSectionDataProvider
     */
    public function testGetSectionData($customerId, array $items, $maxListCount, $expectedCustomerData)
    {
        $this->userContextMock->expects($this->any())
            ->method('getUserId')
            ->willReturn($customerId);

        $this->moduleConfigMock->expects($this->any())
            ->method('getMaxCountRequisitionList')
            ->willReturn($maxListCount);

        $this->searchResultsMock->expects($this->any())
            ->method('getItems')
            ->willReturn($items);

        $this->assertEquals($expectedCustomerData, $this->requisition->getSectionData());
    }

    /**
     * Data provider for getSectionData
     *
     * @return array
     */
    public function getSectionDataProvider()
    {
        return [
            [
                1,
                [
                    $this->buildListMock(1, 'name1'),
                    $this->buildListMock(2, 'name2'),
                    $this->buildListMock(3, 'name3'),
                ],
                5,
                [
                    'items' => [
                        [
                            'id' => 1,
                            'name' => 'name1'
                        ],
                        [
                            'id' => 2,
                            'name' => 'name2'
                        ],
                        [
                            'id' => 3,
                            'name' => 'name3'
                        ],
                    ],
                    'max_allowed_requisition_lists' => 5,
                    'is_enabled' => false
                ]
            ],
            [
                null,
                [],
                2,
                [
                    'items' => [],
                    'max_allowed_requisition_lists' => 2,
                    'is_enabled' => false
                ]
            ],
            [
                1,
                [],
                3,
                [
                    'items' => [],
                    'max_allowed_requisition_lists' => 3,
                    'is_enabled' => false
                ]
            ]
        ];
    }

    /**
     * Build list mock
     *
     * @param int|null $id
     * @param string|null $name
     * @return \Magento\RequisitionList\Api\Data\RequisitionListInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private function buildListMock($id = null, $name = null)
    {
        $listMock = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $listMock->expects($this->any())
            ->method('getId')
            ->willReturn($id);
        $listMock->expects($this->any())
            ->method('getName')
            ->willReturn($name);
        return $listMock;
    }
}
