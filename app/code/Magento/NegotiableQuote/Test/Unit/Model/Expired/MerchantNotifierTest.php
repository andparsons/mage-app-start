<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Expired;

/**
 * MerchantNotifier Test.
 */
class MerchantNotifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\EmailSenderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailSender;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\Expired\MerchantNotifier
     */
    private $merchantNotifier;

    /**
     * Set up.
     * @return void
     */
    protected function setUp()
    {
        $this->emailSender = $this->getMockBuilder(\Magento\NegotiableQuote\Model\EmailSenderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->negotiableQuoteManagement = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class
        )->disableOriginalConstructor()->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->merchantNotifier = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Expired\MerchantNotifier::class,
            [
                'emailSender' => $this->emailSender,
                'scopeConfig' => $this->scopeConfig,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
            ]
        );
    }

    /**
     * Test sendNotification().
     * @return void
     */
    public function testSendNotification()
    {
        $expiredQuoteId = 1;
        $this->scopeConfig->expects($this->atLeastOnce())->method('getValue')->willReturn(true);
        $quote = $this->getMockBuilder(
            \Magento\Quote\Api\Data\CartInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->negotiableQuoteManagement->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')
            ->with($expiredQuoteId)
            ->willReturn($quote);
        $this->merchantNotifier->sendNotification($expiredQuoteId);
    }
}
