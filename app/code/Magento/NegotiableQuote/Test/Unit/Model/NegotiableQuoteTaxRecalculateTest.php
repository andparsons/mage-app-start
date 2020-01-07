<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

use Magento\Authorization\Model\UserContextInterface as UserContext;
use Magento\NegotiableQuote\Model\NegotiableQuoteTaxRecalculate;
use Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuotePrice\ScheduleBulk;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface as NegotiableQuoteRepository;
use Magento\Quote\Api\Data\CartInterface as Cart;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test of NegotiableQuoteTaxRecalculate model.
 */
class NegotiableQuoteTaxRecalculateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NegotiableQuoteRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteRepository;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilder;

    /**
     * @var UserContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var ScheduleBulk|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduleBulk;

    /**
     * @var Cart|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var NegotiableQuoteTaxRecalculate
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->quote = $this->getMockBuilder(Cart::class)->disableOriginalConstructor()->getMockForAbstractClass();
        $this->negotiableQuoteRepository = $this->getMockBuilder(NegotiableQuoteRepository::class)
            ->disableOriginalConstructor()->setMethods(['save', 'getList'])->getMockForAbstractClass();
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'addSortOrder', 'create'])->disableOriginalConstructor()->getMock();
        $this->filterBuilder = $this->getMockBuilder(FilterBuilder::class)->disableOriginalConstructor()->getMock();
        $this->userContext = $this->getMockBuilder(UserContext::class)->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->scheduleBulk = $this->getMockBuilder(ScheduleBulk::class)->disableOriginalConstructor()->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            NegotiableQuoteTaxRecalculate::class,
            [
                'userContext' => $this->userContext,
                'scheduleBulk' => $this->scheduleBulk,
                'negotiableQuoteRepository' => $this->negotiableQuoteRepository,
                'filterBuilder' => $this->filterBuilder,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder
            ]
        );
    }

    /**
     * Test for RecalculateTax method.
     */
    public function testRecalculateTax()
    {
        $this->filterBuilder->expects($this->once())->method('setField')
            ->with('extension_attribute_negotiable_quote.status')->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('setConditionType')->with('nin')->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('setValue')->willReturnSelf();

        $filter = $this->createMock(\Magento\Framework\Api\Filter::class);
        $this->filterBuilder->expects($this->atLeastOnce())->method('create')->willReturn($filter);

        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $searchResults = $this->createMock(\Magento\Framework\Api\SearchResultsInterface::class);

        $this->searchCriteriaBuilder->expects($this->once())->method('addFilter')->with($filter)->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('addSortOrder')->with('entity_id', 'DESC')
            ->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);

        $this->negotiableQuoteRepository->expects($this->once())->method('getList')->with($searchCriteria)
            ->willReturn($searchResults);

        $quoteItems = [$this->quote];
        $searchResults->expects($this->once())->method('getItems')->willReturn($quoteItems);

        $userId = 23;
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);

        $this->scheduleBulk->expects($this->once())->method('execute')->with($quoteItems, $userId)->willReturn($userId);

        $this->model->recalculateTax(true);
    }
}
