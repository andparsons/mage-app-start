<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\History;

/**
 * Class CriteriaBuilderTest
 */
class CriteriaBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\History\CriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SortOrderBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sortOrderBuilder;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->searchCriteriaBuilder = $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->filterBuilder = $this->createMock(\Magento\Framework\Api\FilterBuilder::class);
        $this->sortOrderBuilder = $this->createMock(\Magento\Framework\Api\SortOrderBuilder::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->criteriaBuilder = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\History\CriteriaBuilder::class,
            [
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'filterBuilder' => $this->filterBuilder,
                'sortOrderBuilder' => $this->sortOrderBuilder,
            ]
        );
    }

    /**
     * Test for getQuoteHistoryCriteria() method
     *
     * @return void
     */
    public function testGetQuoteHistoryCriteria()
    {
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);

        $this->sortOrderBuilder->expects($this->any())->method('setField')->will($this->returnSelf());
        $this->sortOrderBuilder->expects($this->any())->method('setDirection')->will($this->returnSelf());
        $this->sortOrderBuilder->expects($this->any())->method('create')->will($this->returnSelf());
        $this->filterBuilder->expects($this->any())->method('setField')->will($this->returnSelf());
        $this->filterBuilder->expects($this->any())->method('setConditionType')->will($this->returnSelf());
        $this->filterBuilder->expects($this->any())->method('setValue')->will($this->returnSelf());
        $this->filterBuilder->expects($this->any())->method('create')->will($this->returnSelf());

        $this->searchCriteriaBuilder->expects($this->any())->method('addFilters')->will($this->returnSelf());
        $this->searchCriteriaBuilder->expects($this->any())->method('addSortOrder')->will($this->returnSelf());
        $this->searchCriteriaBuilder
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($searchCriteria));

        $this->assertEquals($searchCriteria, $this->criteriaBuilder->getQuoteHistoryCriteria(1));
    }

    /**
     * Test for getSystemHistoryCriteria() method
     *
     * @return void
     */
    public function testGetSystemHistoryCriteria()
    {
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);

        $this->filterBuilder->expects($this->any())->method('setField')->will($this->returnSelf());
        $this->filterBuilder->expects($this->any(2))->method('setConditionType')->will($this->returnSelf());
        $this->filterBuilder->expects($this->any(2))->method('setValue')->will($this->returnSelf());
        $this->filterBuilder->expects($this->any(2))->method('create')->will($this->returnSelf());

        $this->searchCriteriaBuilder->expects($this->any())->method('addFilters')->will($this->returnSelf());
        $this->searchCriteriaBuilder
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($searchCriteria));

        $this->assertEquals($searchCriteria, $this->criteriaBuilder->getSystemHistoryCriteria(1));
    }

    /**
     * Test for getQuoteSearchCriteria() method.
     *
     * @return void
     */
    public function testGetQuoteSearchCriteria()
    {
        $quoteId = 1;
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->filterBuilder->expects($this->once())->method('setField')
            ->with('main_table.' . \Magento\Quote\Api\Data\CartInterface::KEY_ENTITY_ID)
            ->will($this->returnSelf());
        $this->filterBuilder->expects($this->any())->method('setConditionType')->will($this->returnSelf());
        $this->filterBuilder->expects($this->once())->method('setValue')
            ->with($quoteId)
            ->will($this->returnSelf());
        $this->filterBuilder->expects($this->any())->method('create')->will($this->returnSelf());
        $this->searchCriteriaBuilder->expects($this->any())->method('addFilters')->will($this->returnSelf());
        $this->searchCriteriaBuilder
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($searchCriteria));

        $this->assertEquals($searchCriteria, $this->criteriaBuilder->getQuoteSearchCriteria(1));
    }
}
