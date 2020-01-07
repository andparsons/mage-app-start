<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Expired\Provider;

/**
 * ExpiredQuoteList Test.
 */
class ExpiredQuoteListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeDate;

    /**
     * @var \Magento\NegotiableQuote\Model\Expired\Provider\ExpiredQuoteList
     */
    private $expiredQuoteList;

    /**
     * Set up.
     * @return void
     */
    protected function setUp()
    {
        $this->negotiableQuoteRepository = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        )->disableOriginalConstructor()->getMock();
        $this->filterBuilder = $this->getMockBuilder(
            \Magento\Framework\Api\FilterBuilder::class
        )->disableOriginalConstructor()->getMock();
        $this->localeDate = $this->getMockBuilder(
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class
        )->disableOriginalConstructor()->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->expiredQuoteList = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Expired\Provider\ExpiredQuoteList::class,
            [
                'negotiableQuoteRepository' => $this->negotiableQuoteRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'filterBuilder' => $this->filterBuilder,
                'localeDate' => $this->localeDate,
            ]
        );
    }

    /**
     * Test getExpiredQuotes() method.
     * @return void
     */
    public function testGetExpiredQuotes()
    {
        $date = $this->getMockBuilder(\DateTime::class)->disableOriginalConstructor()->getMock();
        $date->expects($this->atLeastOnce())->method('format')->willReturn('2020-01-01');
        $this->localeDate->expects($this->once())->method('date')->willReturn($date);
        $this->filterBuilder->expects($this->atLeastOnce())->method('setField')->willReturnSelf();
        $this->filterBuilder->expects($this->atLeastOnce())->method('setConditionType')->willReturnSelf();
        $this->filterBuilder->expects($this->atLeastOnce())->method('setValue')->willReturnSelf();
        $this->filterBuilder->expects($this->atLeastOnce())->method('setField')->willReturnSelf();
        $this->filterBuilder->expects($this->atLeastOnce())->method('create')->willReturnSelf();
        $searchCriteria = $this->getMockBuilder(
            \Magento\Framework\Api\SearchCriteria::class
        )->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('create')->willReturn($searchCriteria);
        $list = $this->getMockBuilder(
            \Magento\Framework\Api\SearchResultsInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->negotiableQuoteRepository->expects($this->atLeastOnce())->method('getList')->willReturn($list);
        $list->expects($this->atLeastOnce())->method('getItems')->willReturn(['items']);
        $this->assertNotEmpty($this->expiredQuoteList->getExpiredQuotes());
    }
}
