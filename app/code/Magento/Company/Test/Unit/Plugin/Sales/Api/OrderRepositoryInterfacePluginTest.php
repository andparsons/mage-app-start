<?php
namespace Magento\Company\Test\Unit\Plugin\Sales\Api;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for OrderRepositoryInterfacePlugin.
 */
class OrderRepositoryInterfacePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\Data\CompanyOrderInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyOrderFactory;

    /**
     * @var \Magento\Sales\Api\Data\OrderExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderExtensionAttributesFactory;

    /**
     * @var \Magento\Company\Model\ResourceModel\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyOrderResource;

    /**
     * @var \Magento\Company\Plugin\Sales\Api\OrderRepositoryInterfacePlugin
     */
    private $orderRepositoryInterfacePlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyOrderFactory = $this
            ->getMockBuilder(\Magento\Company\Api\Data\CompanyOrderInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->orderExtensionAttributesFactory = $this
            ->getMockBuilder(\Magento\Sales\Api\Data\OrderExtensionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->companyOrderResource = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->orderRepositoryInterfacePlugin = $objectManagerHelper->getObject(
            \Magento\Company\Plugin\Sales\Api\OrderRepositoryInterfacePlugin::class,
            [
                'companyOrderFactory' => $this->companyOrderFactory,
                'orderExtensionAttributesFactory' => $this->orderExtensionAttributesFactory,
                'companyOrderResource' => $this->companyOrderResource,
            ]
        );
    }

    /**
     * Test method afterGet.
     *
     * @return void
     */
    public function testAfterGet()
    {
        $orderId = 1;
        $companyId = 2;
        $companyName = 'company';
        $companyOrder = $this->getMockBuilder(\Magento\Company\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getCompanyId', 'getCompanyName'])
            ->getMockForAbstractClass();
        $companyOrder->expects($this->atLeastOnce())->method('getId')->willReturn($orderId);
        $companyOrder->expects($this->once())->method('getCompanyId')->willReturn($companyId);
        $companyOrder->expects($this->once())->method('getCompanyName')->willReturn($companyName);
        $companyOrderExtensionAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyOrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companyOrderExtensionAttributes->expects($this->once())->method('setCompanyId')->with($companyId)
            ->willReturnSelf();
        $companyOrderExtensionAttributes->expects($this->once())->method('setCompanyName')->with($companyName)
            ->willReturnSelf();
        $this->companyOrderFactory->expects($this->atLeastOnce())->method('create')
            ->willReturnOnConsecutiveCalls($companyOrder, $companyOrderExtensionAttributes);
        $this->companyOrderResource->expects($this->once())->method('load')->willReturnSelf();
        $order = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $orderExtensionAttributes = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCompanyOrderAttributes'])
            ->getMockForAbstractClass();
        $orderExtensionAttributes->expects($this->once())->method('setCompanyOrderAttributes')->willReturnSelf();
        $order->expects($this->once())->method('getId')->willReturn($orderId);
        $order->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($orderExtensionAttributes);
        $orderRepository = $this->getMockBuilder(\Magento\Sales\Api\OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->assertInstanceOf(
            \Magento\Sales\Api\Data\OrderInterface::class,
            $this->orderRepositoryInterfacePlugin->afterGet($orderRepository, $order)
        );
    }
}
