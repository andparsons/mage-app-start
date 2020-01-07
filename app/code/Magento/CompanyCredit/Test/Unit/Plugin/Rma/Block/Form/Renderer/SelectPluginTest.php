<?php

namespace Magento\CompanyCredit\Test\Unit\Plugin\Rma\Block\Form\Renderer;

/**
 * Unit test for select payment methods.
 */
class SelectPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreRegistry;

    /**
     * @var \Magento\CompanyCredit\Plugin\Rma\Block\Form\Renderer\SelectPlugin
     */
    private $selectPlugin;

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
        $this->selectPlugin = $objectManager->getObject(
            \Magento\CompanyCredit\Plugin\Rma\Block\Form\Renderer\SelectPlugin::class,
            [
                'coreRegistry' => $this->coreRegistry,
            ]
        );
    }

    /**
     * Test for afterGetOptions method.
     *
     * @return void
     */
    public function testAfterGetOptions()
    {
        $result = [
            ['label' => 'Option 1', 'value' => 1],
            ['label' => 'Option 2', 'value' => 2],
            ['label' => 'Store Credit', 'value' => 3],
        ];
        $select = $this->createMock(\Magento\Rma\Block\Form\Renderer\Select::class);
        $attribute = $this->createMock(\Magento\Eav\Model\Attribute::class);
        $select->expects($this->once())->method('getAttributeObject')->willReturn($attribute);
        $attribute->expects($this->once())->method('getAttributeCode')->willReturn('resolution');
        $order = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $orderPayment = $this->createMock(\Magento\Sales\Api\Data\OrderPaymentInterface::class);
        $this->coreRegistry->expects($this->once())->method('registry')->with('current_order')->willReturn($order);
        $order->expects($this->once())->method('getPayment')->willReturn($orderPayment);
        $orderPayment->expects($this->once())
            ->method('getMethod')
            ->willReturn(\Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME);
        $this->assertEquals(array_slice($result, 0, 2), $this->selectPlugin->afterGetOptions($select, $result));
    }
}
