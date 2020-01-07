<?php
namespace Magento\Company\Test\Unit\Plugin\Sales\Api;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for OrderManagementInterfacePlugin.
 */
class OrderManagementInterfacePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var \Magento\Company\Api\Data\CompanyOrderInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyOrderFactory;

    /**
     * @var \Magento\Sales\Api\Data\OrderExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderExtensionAttributesFactory;

    /**
     * @var \Magento\Company\Plugin\Sales\Api\OrderManagementInterfacePlugin
     */
    private $orderManagementInterfacePlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyManagement = $this->getMockBuilder(\Magento\Company\Api\CompanyManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->orderManagementInterfacePlugin = $objectManagerHelper->getObject(
            \Magento\Company\Plugin\Sales\Api\OrderManagementInterfacePlugin::class,
            [
                'companyManagement' => $this->companyManagement,
                'companyOrderFactory' => $this->companyOrderFactory,
                'orderExtensionAttributesFactory' => $this->orderExtensionAttributesFactory,
            ]
        );
    }

    /**
     * Test method beforePlace().
     *
     * @return void
     */
    public function testBeforePlace()
    {
        $customerId = 1;
        $companyId = 2;
        $companyName = 'company';
        $orderExtensionAttributes = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCompanyOrderAttributes'])
            ->getMockForAbstractClass();
        $orderExtensionAttributes->expects($this->once())->method('setCompanyOrderAttributes')->willReturnSelf();
        $order = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $order->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $order->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($orderExtensionAttributes);
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company->expects($this->once())->method('getId')->willReturn($companyId);
        $company->expects($this->once())->method('getCompanyName')->willReturn($companyName);
        $this->companyManagement->expects($this->once())->method('getByCustomerId')->with($customerId)
            ->willReturn($company);
        $companyOrderExtensionAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyOrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companyOrderExtensionAttributes->expects($this->once())->method('setCompanyId')->with($companyId)
            ->willReturnSelf();
        $companyOrderExtensionAttributes->expects($this->once())->method('setCompanyName')->with($companyName)
            ->willReturnSelf();
        $this->companyOrderFactory->expects($this->once())->method('create')
            ->willReturn($companyOrderExtensionAttributes);
        $orderManagement = $this->getMockBuilder(\Magento\Sales\Api\OrderManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $result = $this->orderManagementInterfacePlugin->beforePlace($orderManagement, $order);

        $this->assertInstanceOf(
            \Magento\Sales\Api\Data\OrderInterface::class,
            $result[0]
        );
    }

    /**
     * Test for afterPlace method.
     *
     * @return void
     */
    public function testAfterPlace()
    {
        $orderId = 1;
        $order = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderExtensionAttributes = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyOrderAttributes'])
            ->getMockForAbstractClass();
        $order->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($orderExtensionAttributes);
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyOrderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', 'setOrderId'])
            ->getMockForAbstractClass();
        $orderExtensionAttributes->expects($this->atLeastOnce())
            ->method('getCompanyOrderAttributes')->willReturn($companyAttributes);
        $order->expects($this->once())->method('getEntityId')->willReturn($orderId);
        $companyAttributes->expects($this->once())->method('setOrderId')->with($orderId)->willReturnSelf();
        $companyAttributes->expects($this->once())->method('save')->willReturnSelf();
        $orderManagement = $this->getMockBuilder(\Magento\Sales\Api\OrderManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals($order, $this->orderManagementInterfacePlugin->afterPlace($orderManagement, $order));
    }
}
