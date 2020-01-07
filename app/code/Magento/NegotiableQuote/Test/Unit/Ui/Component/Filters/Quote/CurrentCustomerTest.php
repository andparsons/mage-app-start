<?php
namespace Magento\NegotiableQuote\Test\Unit\Ui\Component\Filters\Quote;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Company\Api\AuthorizationInterface;
use Magento\Company\Model\Company\Structure;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\NegotiableQuote\Ui\Component\Filters\Quote\CurrentCustomer;
use Magento\Ui\Component\Filters\FilterModifier;

/**
 * Unit test for `Magento\NegotiableQuote\Ui\Component\Filters\Quote\CurrentCustomer` class.
 */
class CurrentCustomerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilderMock;

    /**
     * @var FilterModifier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterModifierMock;

    /**
     * @var UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContextMock;

    /**
     * @var Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureMock;

    /**
     * @var AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterBuilderMock = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterModifierMock = $this->getMockBuilder(FilterModifier::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userContextMock = $this->getMockBuilder(UserContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->structureMock = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authorizationMock = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test for method `prepare`.
     *
     * @return void
     */
    public function testPrepare()
    {
        $currentCustomerFilter = $this->setUpFilter(
            ['name' => 'is_customer_quote'],
            ['is_customer_quote' => true]
        );

        $this->userContextMock->expects($this->atLeastOnce())
            ->method('getUserId')
            ->willReturn(1);
        $this->structureMock->expects($this->atLeastOnce())
            ->method('getAllowedChildrenIds')
            ->willReturn([2, 3]);
        $this->authorizationMock->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_NegotiableQuote::view_quotes_sub')
            ->willReturn(true);
        $this->filterModifierMock->expects($this->atLeastOnce())
            ->method('applyFilterModifier');
        $dataProviderMock = $this->getMockBuilder(DataProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getDataProvider')
            ->willReturn($dataProviderMock);
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getProcessor')
            ->willReturn($processorMock);
        $filterMock = $this->getMockBuilder(\Magento\Framework\Api\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterBuilderMock->expects($this->atLeastOnce())
            ->method('setConditionType')
            ->with('eq')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->atLeastOnce())
            ->method('setField')
            ->with('customer_id')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->atLeastOnce())
            ->method('setValue')
            ->with(1)
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($filterMock);

        $currentCustomerFilter->prepare();
    }

    /**
     * Test for method `prepare` without filter.
     *
     * @return void
     */
    public function testPrepareWithoutFilter()
    {
        $currentCustomerFilter = $this->setUpFilter(
            ['name' => 'is_customer_quote'],
            []
        );

        $this->userContextMock->expects($this->atLeastOnce())
            ->method('getUserId')
            ->willReturn(1);
        $this->structureMock->expects($this->atLeastOnce())
            ->method('getAllowedChildrenIds')
            ->willReturn([2, 3]);
        $this->authorizationMock->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_NegotiableQuote::view_quotes_sub')
            ->willReturn(true);

        $dataProviderMock = $this->getMockBuilder(DataProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getDataProvider')
            ->willReturn($dataProviderMock);
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getProcessor')
            ->willReturn($processorMock);

        $dataProviderMock->expects($this->never())
            ->method('addFilter');

        $currentCustomerFilter->prepare();
    }

    /**
     * Set up filter.
     *
     * @param array $data
     * @param array $filterData
     * @return CurrentCustomer
     */
    private function setUpFilter(array $data, array $filterData)
    {
        $filter = (new ObjectManagerHelper($this))->getObject(
            CurrentCustomer::class,
            [
                'context' => $this->contextMock,
                'filterBuilder' => $this->filterBuilderMock,
                'filterModifier' => $this->filterModifierMock,
                'userContext' => $this->userContextMock,
                'structure' => $this->structureMock,
                'authorization' => $this->authorizationMock,
                'filterData' => $filterData,
                '_data' => $data
            ]
        );

        return $filter;
    }
}
