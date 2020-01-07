<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Plugin\Quote\Model;

/**
 * Test for Magento\NegotiableQuote\Model\Plugin\Quote\Model\LoadHandlerPlugin class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoadHandlerPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\Data\CartExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartExtensionFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Model\Plugin\Quote\Model\LoadHandlerPlugin
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->cartExtensionFactory = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->restriction = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->negotiableQuoteRepository = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\NegotiableQuoteRepository::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Plugin\Quote\Model\LoadHandlerPlugin::class,
            [
                'cartExtensionFactory' => $this->cartExtensionFactory,
                'restriction' => $this->restriction,
                'negotiableQuoteRepository' => $this->negotiableQuoteRepository,
                'request' => $this->request
            ]
        );
    }

    /**
     * Test beforeLoad method.
     *
     * @return void
     */
    public function testBeforeLoad()
    {
        $quoteId = 1;
        $subject = $this->getMockBuilder(\Magento\Quote\Model\QuoteRepository\LoadHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->setMethods(
                [
                    'getId',
                    'getExtensionAttributes',
                    'unsetData',
                    'setExtensionAttributes',
                    'setIsActive',
                    'getCustomer',
                    'getCustomerGroupId'
                ]
            )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $cartExtension = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtension::class)
            ->disableOriginalConstructor()
            ->setMethods(['setNegotiableQuote'])
            ->getMock();
        $quote->expects($this->atLeastOnce())
            ->method('getCustomer')
            ->willReturn($customer);
        $quote->expects($this->atLeastOnce())
            ->method('getCustomerGroupId')
            ->willReturn(1);
        $customer->expects($this->atLeastOnce())
            ->method('getGroupId')
            ->willReturn(2);
        $quote->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturnOnConsecutiveCalls($extensionAttributes, $extensionAttributes, null, $extensionAttributes);
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')
            ->willReturnOnConsecutiveCalls(null, $negotiableQuote);
        $quote->expects($this->atLeastOnce())->method('getId')->willReturn($quoteId);
        $this->negotiableQuoteRepository->expects($this->once())
            ->method('getById')
            ->with($quoteId)
            ->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->atLeastOnce())->method('getIsRegularQuote')->willReturn(true);
        $this->restriction->expects($this->once())->method('setQuote')->with($quote)->willReturnSelf();
        $this->cartExtensionFactory->expects($this->once())->method('create')->willReturn($cartExtension);
        $cartExtension->expects($this->once())->method('setNegotiableQuote')->with($negotiableQuote)->willReturnSelf();
        $quote->expects($this->once())->method('setExtensionAttributes')->with($cartExtension)->willReturnSelf();
        $quote->expects($this->once())->method('setIsActive')->with(true)->willReturnSelf();

        $this->assertEquals([$quote], $this->plugin->beforeLoad($subject, $quote));
    }

    /**
     * Test beforeLoad with negotiable quote.
     *
     * @return void
     */
    public function testBeforeLoadWithNegotiableQuote()
    {
        $subject = $this->getMockBuilder(\Magento\Quote\Model\QuoteRepository\LoadHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->once())
            ->method('getNegotiableQuote')
            ->willReturn($negotiableQuote);

        $this->assertEquals([$quote], $this->plugin->beforeLoad($subject, $quote));
    }

    /**
     * Test beforeLoad with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Negotiated quote not found.
     */
    public function testBeforeLoadWithException()
    {
        $quoteId = 1;
        $exception = new \Exception();
        $subject = $this->getMockBuilder(\Magento\Quote\Model\QuoteRepository\LoadHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturnOnConsecutiveCalls($extensionAttributes, $extensionAttributes, null, $extensionAttributes);
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')
            ->willReturnOnConsecutiveCalls(null, $negotiableQuote);
        $quote->expects($this->atLeastOnce())->method('getId')->willReturn($quoteId);
        $this->negotiableQuoteRepository->expects($this->once())
            ->method('getById')
            ->with($quoteId)
            ->willThrowException($exception);

        $this->plugin->beforeLoad($subject, $quote);
    }
}
