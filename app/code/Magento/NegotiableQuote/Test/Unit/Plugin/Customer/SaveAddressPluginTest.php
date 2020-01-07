<?php
declare(strict_types=1);

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Customer;

/**
 * Unit test for Magento\NegotiableQuote\Plugin\Customer\SaveAddressPlugin class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveAddressPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteAddress;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorization;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Customer\SaveAddressPlugin
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->negotiableQuoteAddress = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->authorization = $this->getMockBuilder(\Magento\Company\Api\AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Plugin\Customer\SaveAddressPlugin::class,
            [
                'context' => $this->context,
                'negotiableQuoteAddress' => $this->negotiableQuoteAddress,
                'logger' => $this->logger,
                'authorization' => $this->authorization,
            ]
        );
    }

    /**
     * Test aroundSave method.
     *
     * @return void
     */
    public function testAfterSave()
    {
        $quoteId = 1;
        $subject = $this->getMockBuilder(\Magento\Customer\Api\AddressRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->context->expects($this->once())->method('getRequest')->willReturn($request);
        $request->expects($this->once())
            ->method('getParam')
            ->with('quoteId')
            ->willReturn($quoteId);
        $this->negotiableQuoteAddress->expects($this->once())
            ->method('updateQuoteShippingAddress')
            ->with($quoteId, $address)
            ->willReturn(true);
        $this->authorization->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_NegotiableQuote::manage')
            ->willReturn(true);

        $this->assertEquals($address, $this->plugin->afterSave($subject, $address));
    }

    /**
     * Test aroundSave method without quote id.
     *
     * @return void
     */
    public function testAfterSaveWithoutQuoteId()
    {
        $subject = $this->getMockBuilder(\Magento\Customer\Api\AddressRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->context->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);
        $request->expects($this->once())
            ->method('getParam')
            ->with('quoteId')
            ->willReturn(null);

        $this->assertEquals($address, $this->plugin->afterSave($subject, $address));
    }

    /**
     * Test aroundSave method with NoSuchEntityException.
     *
     * @return void
     */
    public function testAfterSaveWithNoSuchEntityException()
    {
        $quoteId = 1;
        $subject = $this->getMockBuilder(\Magento\Customer\Api\AddressRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $exception = new \Magento\Framework\Exception\NoSuchEntityException(__('No such entity.'));
        $this->context->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);
        $request->expects($this->once())
            ->method('getParam')
            ->with('quoteId')
            ->willReturn($quoteId);
        $this->negotiableQuoteAddress->expects($this->once())
            ->method('updateQuoteShippingAddress')
            ->with($quoteId, $address)
            ->willThrowException($exception);
        $this->context->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($messageManager);
        $messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Requested quote was not found'))
            ->willReturnSelf();
        $this->authorization->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_NegotiableQuote::manage')
            ->willReturn(true);

        $this->assertEquals($address, $this->plugin->afterSave($subject, $address));
    }

    /**
     * Test aroundSave method with Exception.
     *
     * @return void
     */
    public function testAfterSaveWithException()
    {
        $quoteId = 1;
        $subject = $this->getMockBuilder(\Magento\Customer\Api\AddressRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $exception = new \Exception();
        $this->context->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);
        $request->expects($this->once())
            ->method('getParam')
            ->with('quoteId')
            ->willReturn($quoteId);
        $this->negotiableQuoteAddress->expects($this->once())
            ->method('updateQuoteShippingAddress')
            ->with($quoteId, $address)
            ->willThrowException($exception);
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($exception);
        $this->context->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($messageManager);
        $messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Unable to update shipping address'))
            ->willReturnSelf();
        $this->authorization->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_NegotiableQuote::manage')
            ->willReturn(true);

        $this->assertEquals($address, $this->plugin->afterSave($subject, $address));
    }

    /**
     * Test aroundSave method without quote id.
     *
     * @return void
     */
    public function testAfterSaveWithoutPermissions()
    {
        $quoteId = 1;
        $subject = $this->getMockBuilder(\Magento\Customer\Api\AddressRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->context->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);
        $request->expects($this->once())
            ->method('getParam')
            ->with('quoteId')
            ->willReturn($quoteId);
        $this->authorization->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_NegotiableQuote::manage')
            ->willReturn(false);

        $this->assertEquals($address, $this->plugin->afterSave($subject, $address));
    }
}
