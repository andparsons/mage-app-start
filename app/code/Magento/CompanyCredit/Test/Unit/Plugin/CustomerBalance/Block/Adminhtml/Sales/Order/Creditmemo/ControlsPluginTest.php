<?php

namespace Magento\CompanyCredit\Test\Unit\Plugin\CustomerBalance\Block\Adminhtml\Sales\Order\Creditmemo;

/**
 * Unit test for refund to customer balance.
 */
class ControlsPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreRegistry;

    /**
     * @var \Magento\CompanyCredit\Plugin\CustomerBalance\Block\Adminhtml\Sales\Order\Creditmemo\ControlsPlugin
     */
    private $controlsPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->coreRegistry = $this->createMock(\Magento\Framework\Registry::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controlsPlugin = $objectManager->getObject(
            \Magento\CompanyCredit\Plugin\CustomerBalance\Block\Adminhtml\Sales\Order\Creditmemo\ControlsPlugin::class,
            [
                'coreRegistry' => $this->coreRegistry,
            ]
        );
    }

    /**
     * Test for afterCanRefundToCustomerBalance method.
     *
     * @return void
     */
    public function testAfterCanRefundToCustomerBalance()
    {
        $subject = $this->createMock(
            \Magento\CustomerBalance\Block\Adminhtml\Sales\Order\Creditmemo\Controls::class
        );
        $creditmemo = $this->getMockForAbstractClass(
            \Magento\Sales\Api\Data\CreditmemoInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getOrder']
        );
        $order = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $orderPayment = $this->createMock(\Magento\Sales\Api\Data\OrderPaymentInterface::class);
        $this->coreRegistry->expects($this->once())
            ->method('registry')->with('current_creditmemo')->willReturn($creditmemo);
        $creditmemo->expects($this->once())->method('getOrder')->willReturn($order);
        $order->expects($this->once())->method('getPayment')->willReturn($orderPayment);
        $orderPayment->expects($this->once())
            ->method('getMethod')
            ->willReturn(\Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME);
        $this->assertFalse($this->controlsPlugin->afterCanRefundToCustomerBalance($subject, true));
    }
}
