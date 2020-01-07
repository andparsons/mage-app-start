<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Query;

/**
 * Test for Magento\NegotiableQuote\Model\Query\GetList class.
 */
class GetListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\Framework\Api\SearchResultsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    /**
     * @var \Magento\NegotiableQuote\Model\Query\GetList
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->extensionAttributesJoinProcessor = $this->getMockBuilder(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchResultsFactory = $this->getMockBuilder(\Magento\Framework\Api\SearchResultsFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->collectionFactory = $this->getMockBuilder(
            \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->restriction = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->negotiableQuoteManagement = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->collectionProcessor = $this->getMockBuilder(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Query\GetList::class,
            [
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessor,
                'searchResultsFactory' => $this->searchResultsFactory,
                'collectionFactory' => $this->collectionFactory,
                'restriction' => $this->restriction,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'collectionProcessor' => $this->collectionProcessor,
            ]
        );
    }

    /**
     * Test getList method.
     *
     * @return void
     */
    public function testGetList()
    {
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $searchResult = $this->getMockBuilder(\Magento\Framework\Api\SearchResults::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection = $this->getMockBuilder(\Magento\Quote\Model\ResourceModel\Quote\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $item = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $snapshot = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchResultsFactory->expects($this->once())->method('create')->willReturn($searchResult);
        $searchResult->expects($this->once())->method('setSearchCriteria')->with($searchCriteria)->willReturnSelf();
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->extensionAttributesJoinProcessor->expects($this->once())->method('process')->with($collection);
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with(
                'extension_attribute_negotiable_quote.is_regular_quote',
                ['eq' => 1]
            )
            ->willReturnSelf();
        $this->collectionProcessor->expects($this->once())->method('process')->with($searchCriteria, $collection);
        $collection->expects($this->once())->method('getItems')->willReturn([$item]);
        $this->restriction->expects($this->once())->method('setQuote')->with($item)->willReturnSelf();
        $this->restriction->expects($this->once())->method('isLockMessageDisplayed')->willReturn(true);
        $item->expects($this->once())->method('getId')->willReturn(1);
        $this->negotiableQuoteManagement->expects($this->once())->method('getSnapshotQuote')->willReturn($snapshot);
        $searchResult->expects($this->once())->method('setItems')->with([$snapshot])->willReturnSelf();
        $collection->expects($this->once())->method('getSize')->willReturn(1);
        $searchResult->expects($this->once())->method('setTotalCount')->with(1)->willReturnSelf();

        $this->assertSame($searchResult, $this->model->getList($searchCriteria, true));
    }

    /**
     * Test getListByCustomerId method.
     *
     * @return void
     */
    public function testGetListByCustomerId()
    {
        $customerId = 1;
        $collection = $this->getMockBuilder(\Magento\Quote\Model\ResourceModel\Quote\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->extensionAttributesJoinProcessor->expects($this->once())->method('process')->with($collection);
        $collection->expects($this->atLeastOnce())
            ->method('addFieldToFilter')
            ->withConsecutive(
                [
                    'extension_attribute_negotiable_quote.is_regular_quote', ['eq' => 1]
                ],
                [
                    'main_table.customer_id', ['eq' => $customerId]
                ]
            )
            ->willReturnSelf();
        $collection->expects($this->once())->method('getItems')->willReturn([$quote]);

        $this->assertSame([$quote], $this->model->getListByCustomerId($customerId));
    }
}
