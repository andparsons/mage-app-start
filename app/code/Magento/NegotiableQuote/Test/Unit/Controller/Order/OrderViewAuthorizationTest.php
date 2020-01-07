<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class OrderViewAuthorizationTest
 */
class OrderViewAuthorizationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Controller\Order\OrderViewAuthorization
     */
    private $orderViewAuthorization;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureMock;

    /**
     * @var \Magento\Sales\Model\Order\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderConfigMock;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContextMock;

    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        $this->structureMock = $this->getMockBuilder(\Magento\Company\Model\Company\Structure::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllowedChildrenIds'])
            ->getMock();
        $this->orderConfigMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getVisibleOnFrontStatuses'])
            ->getMock();
        $this->userContextMock = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserId'])
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->orderViewAuthorization = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Controller\Order\OrderViewAuthorization::class,
            [
                'structure' => $this->structureMock,
                'orderConfig' => $this->orderConfigMock,
                'userContext' => $this->userContextMock
            ]
        );
    }

    /**
     * Test for canView() method
     *
     * @param int $orderCustomerId
     * @param bool $result
     * @dataProvider canViewDataProvider
     * @return void
     */
    public function testCanView($orderCustomerId, $result)
    {
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getCustomerId', 'getStatus'])
            ->getMock();
        $this->userContextMock->expects($this->any())
            ->method('getUserId')
            ->willReturn(1);
        $this->orderConfigMock->expects($this->any())
            ->method('getVisibleOnFrontStatuses')
            ->willReturn([
                'completed',
                'viewed'
            ]);
        $this->structureMock->expects($this->any())
            ->method('getAllowedChildrenIds')
            ->willReturn([
                5,
                6
            ]);
        $orderMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $orderMock->expects($this->any())
            ->method('getCustomerId')
            ->willReturn($orderCustomerId);
        $orderMock->expects($this->any())
            ->method('getStatus')
            ->willReturn('completed');

        $this->assertEquals($result, $this->orderViewAuthorization->canView($orderMock));
    }

    /**
     * DataProvider for testAdminSend
     *
     * @return array
     */
    public function canViewDataProvider()
    {
        return [
            [1000, false],
            [5, true]
        ];
    }
}
