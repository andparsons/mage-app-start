<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Sales\Block\Order;

/**
 * Class HistoryPluginTest
 */
class HistoryPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContextMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderCollectionMock;

    /**
     * @var \Magento\NegotiableQuote\Block\Order\OwnerFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ownerFilterBlockMock;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Sales\Block\Order\HistoryPlugin
     */
    private $historyPlugin;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $createdBy = 1;
        $this->userContextMock = $this->getMockForAbstractClass(
            \Magento\Authorization\Model\UserContextInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getUserId']
        );
        $this->requestMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getParam']
        );
        $this->ownerFilterBlockMock = $this->createMock(\Magento\NegotiableQuote\Block\Order\OwnerFilter::class);
        $this->requestMock->expects($this->any())->method('getParam')->willReturn($createdBy);
        $this->userContextMock->expects($this->any())->method('getUserId')->willReturn(1);
        $this->ownerFilterBlockMock->expects($this->any())->method('getShowMyParam')->willReturn($createdBy);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->historyPlugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Plugin\Sales\Block\Order\HistoryPlugin::class,
            [
                'request' => $this->requestMock,
                'userContext' => $this->userContextMock,
                'ownerFilterBlock' => $this->ownerFilterBlockMock,
            ]
        );
    }

    /**
     * Test for method afterGetOrders
     * @param bool|\Magento\Sales\Model\ResourceModel\Order\Collection $resultIn
     * @param bool|\Magento\Sales\Model\ResourceModel\Order\Collection $resultOut
     * @return void
     * @dataProvider dataProviderAfterGetOrders
     */
    public function testAfterGetOrders($resultIn, $resultOut)
    {
        $historyMock = $this->createMock(\Magento\Sales\Block\Order\History::class);

        $this->assertEquals($this->historyPlugin->afterGetOrders($historyMock, $resultIn), $resultOut);
    }

    /**
     * Data provider afterGetOrders
     *
     * @return array
     */
    public function dataProviderAfterGetOrders()
    {
        $this->orderCollectionMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
        return [
            [false, false],
            [$this->orderCollectionMock, $this->orderCollectionMock]
        ];
    }
}
