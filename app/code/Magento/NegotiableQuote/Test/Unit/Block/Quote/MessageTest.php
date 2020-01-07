<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Quote;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class MessageTest
 */
class MessageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Block\Quote\Message|\PHPUnit_Framework_MockObject_MockObject
     */
    private $message;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteHelperMock;

    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        $this->quoteHelperMock = $this->getMockBuilder(\Magento\NegotiableQuote\Helper\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['isLockMessageDisplayed', 'isViewedByOwner', 'isExpiredMessageDisplayed'])
            ->getMockForAbstractClass();
    }

    /**
     * Create instance
     *
     * @return void
     */
    private function createInstance()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->message = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Block\Quote\Message::class,
            [
                'quoteHelper' => $this->quoteHelperMock
            ]
        );
    }

    /**
     * Test for getMessages() method
     *
     * @return void
     */
    public function testGetMessages()
    {
        $expectedMessages = [
            __('This quote is currently locked for editing. It will become available once released by the Merchant.'),
            __(
                'Your quote has expired and the product prices have been updated as per the latest prices in your'
                . ' catalog. You can either re-submit the quote to seller for further negotiation or go to checkout.'
            ),
            __('You are not an owner of this quote. You cannot edit it or take any actions on it.')
        ];

        $this->quoteHelperMock->expects($this->any())
            ->method('isLockMessageDisplayed')
            ->willReturn(true);
        $this->quoteHelperMock->expects($this->any())
            ->method('isViewedByOwner')
            ->willReturn(false);
        $this->quoteHelperMock->expects($this->any())
            ->method('isExpiredMessageDisplayed')
            ->willReturn(true);

        $this->createInstance();
        $this->assertEquals($expectedMessages, $this->message->getMessages());
    }
}
