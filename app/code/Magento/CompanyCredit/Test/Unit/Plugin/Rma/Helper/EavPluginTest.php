<?php

namespace Magento\CompanyCredit\Test\Unit\Plugin\Rma\Helper;

/**
 * Unit test for attribute option values.
 */
class EavPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreRegistry;

    /**
     * @var \Magento\CompanyCredit\Plugin\Rma\Helper\EavPlugin
     */
    private $eavPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->coreRegistry = $this->createMock(
            \Magento\Framework\Registry::class
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->eavPlugin = $objectManager->getObject(
            \Magento\CompanyCredit\Plugin\Rma\Helper\EavPlugin::class,
            [
                'coreRegistry' => $this->coreRegistry,
            ]
        );
    }

    /**
     * Test for aroundGetAttributeOptionValues method.
     *
     * @return void
     */
    public function testAroundGetAttributeOptionValues()
    {
        $result = ['Option 1', 'Option 2', 'Store Credit'];
        $helper = $this->createMock(\Magento\Rma\Helper\Eav::class);
        $method = function () use ($result) {
            return $result;
        };
        $order = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $orderPayment = $this->createMock(\Magento\Sales\Api\Data\OrderPaymentInterface::class);
        $this->coreRegistry->expects($this->once())->method('registry')->with('current_order')->willReturn($order);
        $order->expects($this->once())->method('getPayment')->willReturn($orderPayment);
        $orderPayment->expects($this->once())
            ->method('getMethod')
            ->willReturn(\Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME);
        $this->assertEquals(
            array_slice($result, 0, 2),
            $this->eavPlugin->aroundGetAttributeOptionValues($helper, $method, 'resolution')
        );
    }
}
