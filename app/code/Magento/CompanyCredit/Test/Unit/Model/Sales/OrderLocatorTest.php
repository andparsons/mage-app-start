<?php
namespace Magento\CompanyCredit\Test\Unit\Model\Sales;

use Magento\CompanyCredit\Model\Sales\OrderLocator;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Unit test for \Magento\CompanyCredit\Model\Sales\OrderLocator class.
 */
class OrderLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OrderLocator
     */
    private $orderLocator;

    /**
     * @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderLocator = (new ObjectManager($this))->getObject(
            OrderLocator::class,
            [
                'orderRepository' => $this->orderRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock
            ]
        );
    }

    /**
     * Test for `getOrderByIncrementId` method.
     *
     * @return void
     */
    public function testGetOrderByIncrementId()
    {
        $incrementId = 1;

        $order = $this->getMockBuilder(OrderInterface::class)
            ->getMockForAbstractClass();

        $searchResult = $this->getMockBuilder(OrderSearchResultInterface::class)
            ->getMockForAbstractClass();
        $searchResult->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn([$order]);

        $this->mockGetList($searchResult);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with(OrderInterface::INCREMENT_ID, $incrementId)
            ->willReturnSelf();

        $this->assertEquals(
            $order,
            $this->orderLocator->getOrderByIncrementId($incrementId)
        );
    }

    /**
     * Test for getOrderByIncrementId method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with increment_id = 1
     */
    public function testGetOrderByIncrementIdWithException()
    {
        $incrementId = 1;

        $searchResult = $this->getMockBuilder(OrderSearchResultInterface::class)
            ->getMockForAbstractClass();
        $searchResult->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn([]);

        $this->mockGetList($searchResult);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with(OrderInterface::INCREMENT_ID, $incrementId)
            ->willReturnSelf();

        $this->orderLocator->getOrderByIncrementId($incrementId);
    }

    /**
     * Mock getList.
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $result
     * @return void
     */
    private function mockGetList(\PHPUnit_Framework_MockObject_MockObject $result)
    {
        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteria);

        $this->orderRepositoryMock->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn($result);
    }
}
