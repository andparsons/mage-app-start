<?php

namespace Magento\CompanyCredit\Test\Unit\Plugin\Sales\Block\Adminhtml\Order\Creditmemo\Create;

/**
 * Unit test for add labels to items.
 */
class ItemsPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Plugin\Sales\Block\Adminhtml\Order\Creditmemo\Create\ItemsPlugin
     */
    private $itemsPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->coreRegistry = $this->createMock(\Magento\Framework\Registry::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->itemsPlugin = $objectManager->getObject(
            \Magento\CompanyCredit\Plugin\Sales\Block\Adminhtml\Order\Creditmemo\Create\ItemsPlugin::class
        );
    }

    /**
     * Test for beforeToHtml method.
     *
     * @return void
     */
    public function testBeforeToHtml()
    {
        $subject = $this->createMock(
            \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items::class
        );
        $refundBtn = $this->createPartialMock(
            \Magento\Framework\View\Element\AbstractBlock::class,
            ['setLabel']
        );
        $order = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $orderPayment = $this->createMock(\Magento\Sales\Api\Data\OrderPaymentInterface::class);
        $subject->expects($this->once())->method('getOrder')->willReturn($order);
        $order->expects($this->once())->method('getPayment')->willReturn($orderPayment);
        $orderPayment->expects($this->once())
            ->method('getMethod')
            ->willReturn(\Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME);
        $subject->expects($this->once())->method('getChildBlock')->with('submit_offline')->willReturn($refundBtn);
        $refundBtn->expects($this->once())->method('setLabel')->with(__('Refund to Company Credit'))->willReturnSelf();
        $this->itemsPlugin->beforeToHtml($subject);
    }
}
