<?php

namespace Magento\CustomerBalance\Test\Unit\Model\Plugin;

use Magento\CustomerBalance\Model\Plugin\OrderRepository;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OrderRepository
     */
    private $plugin;

    /**
     * @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var OrderExtension|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributeMock;

    /**
     * @var OrderExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderExtensionFactoryMock;

    protected function setUp()
    {
        $this->subjectMock = $this->getMockForAbstractClass(OrderRepositoryInterface::class);
        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->setMethods([
                'getExtensionAttributes',
                'setExtensionAttributes',
                'setCustomerBalanceAmount',
                'setBaseCustomerBalanceAmount',
                'getBaseCustomerBalanceAmount',
                'getCustomerBalanceAmount',
                'getBsCustomerBalTotalRefunded',
                'getBaseCustomerBalanceInvoiced',
                'getCustomerBalanceInvoiced',
                'getBaseCustomerBalanceRefunded',
                'getCustomerBalanceRefunded',
                'getCustomerBalTotalRefunded',
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->extensionAttributeMock = $this->getMockBuilder(OrderExtension::class)
            ->setMethods([
                'getCustomerBalanceAmount',
                'getBaseCustomerBalanceAmount',
                'setCustomerBalanceAmount',
                'setBaseCustomerBalanceAmount',
                'setBaseCustomerBalanceTotalRefunded',
                'setBaseCustomerBalanceInvoiced',
                'setCustomerBalanceInvoiced',
                'setBaseCustomerBalanceRefunded',
                'setCustomerBalanceRefunded',
                'setCustomerBalanceTotalRefunded',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderExtensionFactoryMock = $this->getMockBuilder(OrderExtensionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new OrderRepository(
            $this->orderExtensionFactoryMock
        );
    }

    public function testAfterGet()
    {
        $customerBalanceAmount = 10;
        $baseCustomerBalanceAmount = 15;

        $this->orderMock->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributeMock);
        $this->orderMock->expects(static::once())
            ->method('getCustomerBalanceAmount')
            ->willReturn($customerBalanceAmount);
        $this->orderMock->expects(static::once())
            ->method('getBaseCustomerBalanceAmount')
            ->willReturn($baseCustomerBalanceAmount);
        $this->orderMock->expects(static::once())
            ->method('getBsCustomerBalTotalRefunded')
            ->willReturn($customerBalanceAmount);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setBaseCustomerBalanceTotalRefunded')
            ->with($customerBalanceAmount)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getBaseCustomerBalanceInvoiced')
            ->willReturn($customerBalanceAmount);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setCustomerBalanceInvoiced')
            ->with($customerBalanceAmount)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getCustomerBalanceInvoiced')
            ->willReturn($customerBalanceAmount);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setBaseCustomerBalanceInvoiced')
            ->with($customerBalanceAmount)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getBaseCustomerBalanceRefunded')
            ->willReturn($customerBalanceAmount);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setBaseCustomerBalanceRefunded')
            ->with($customerBalanceAmount)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getCustomerBalanceRefunded')
            ->willReturn($customerBalanceAmount);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setCustomerBalanceRefunded')
            ->with($customerBalanceAmount)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getCustomerBalTotalRefunded')
            ->willReturn($customerBalanceAmount);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setCustomerBalanceTotalRefunded')
            ->with($customerBalanceAmount)
            ->willReturnSelf();
        $this->extensionAttributeMock->expects(static::once())
            ->method('setCustomerBalanceAmount')
            ->with($customerBalanceAmount)
            ->willReturnSelf();
        $this->extensionAttributeMock->expects(static::once())
            ->method('setBaseCustomerBalanceAmount')
            ->with($baseCustomerBalanceAmount)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('setExtensionAttributes')
            ->with($this->extensionAttributeMock)
            ->willReturnSelf();

        $this->plugin->afterGet($this->subjectMock, $this->orderMock);
    }
}
