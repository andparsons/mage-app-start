<?php
namespace Magento\NegotiableQuote\Test\Unit\Model;

use Magento\NegotiableQuote\Model\NegotiableQuoteConfigProvider;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Test for NegotiableQuoteConfigProvider.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NegotiableQuoteConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NegotiableQuoteConfigProvider
     */
    private $negotiableQuoteConfigProvider;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $context;

    /**
     * @var NegotiableQuoteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteRepository;

    /**
     * @var CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressRepository;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $address;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $objectManager->getObject(\Magento\Framework\App\Action\Context::class);

        $this->negotiableQuoteRepository = $this->getMockForAbstractClass(
            NegotiableQuoteRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->quoteRepository = $this->getMockForAbstractClass(
            CartRepositoryInterface::class,
            ['get'],
            '',
            false
        );
        $this->addressRepository = $this->getMockForAbstractClass(
            AddressRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->session = $this->createMock(Session::class);

        $this->address = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\AddressInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getCustomerAddressId', 'getShippingMethod', 'getData']
        );

        $this->quote = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getShippingAddress', 'getPayment', 'getExtensionAttributes']
        );

        $this->negotiableQuoteConfigProvider = $objectManager->getObject(
            NegotiableQuoteConfigProvider::class,
            [
                'context' => $this->context,
                'negotiableQuoteRepository' => $this->negotiableQuoteRepository,
                'quoteRepository' => $this->quoteRepository,
                'addressRepository' => $this->addressRepository,
                'session' => $this->session,
            ]
        );
    }

    /**
     * Test for method getConfig.
     *
     * @return void
     */
    public function testGetConfig()
    {
        $quoteValue = 42;
        $this->session->expects($this->any())->method('getQuoteId')->willReturn($quoteValue);

        $orderPayment = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethod'])
            ->getMockForAbstractClass();

        $this->quoteRepository->expects($this->any())->method('get')->willReturn($this->quote);
        $this->quote->expects($this->any())->method('getShippingAddress')->willReturn($this->address);
        $this->address->expects($this->any())->method('getCustomerAddressId')->willReturn($quoteValue);
        $this->addressRepository->expects($this->any())->method('getById')
            ->with($quoteValue)->willReturn($this->address);
        $this->quote->expects($this->any())->method('getPayment')->willReturn($orderPayment);
        $orderPayment->expects($this->any())->method('getMethod')->willReturn($quoteValue);

        $this->assertArrayHasKey('isNegotiableQuote', $this->negotiableQuoteConfigProvider->getConfig());
    }

    /**
     * Test for method getConfig without quoteId.
     *
     * @return void
     */
    public function testGetConfigNoQuoteId()
    {
        $quoteValue = 42;
        $this->session->expects($this->any())->method('getQuoteId')->willReturn(null);

        $orderPayment = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethod'])
            ->getMockForAbstractClass();

        $this->quoteRepository->expects($this->any())->method('get')->willReturn($this->quote);
        $this->quote->expects($this->any())->method('getShippingAddress')->willReturn($this->address);
        $this->address->expects($this->any())->method('getCustomerAddressId')->willReturn($quoteValue);
        $this->addressRepository->expects($this->any())->method('getById')
            ->with($quoteValue)->willReturn($this->address);
        $this->quote->expects($this->any())->method('getPayment')->willReturn($orderPayment);
        $orderPayment->expects($this->any())->method('getMethod')->willReturn($quoteValue);

        $this->assertArrayHasKey('isNegotiableQuote', $this->negotiableQuoteConfigProvider->getConfig());
    }

    /**
     * Test for method getConfig catching NoSuchEntityException.
     *
     * @return void
     */
    public function testGetConfigWithException()
    {
        $exception = new NoSuchEntityException();
        $this->quoteRepository->expects($this->any())->method('get')->willReturn($this->quote);
        $this->quote->expects($this->any())->method('getShippingAddress')->willReturn($this->address);
        $this->address->expects($this->any())->method('getCustomerAddressId')->willThrowException($exception);
        $this->address->expects($this->any())->method('getData')->willReturn([]);

        $this->assertArrayHasKey('isNegotiableQuote', $this->negotiableQuoteConfigProvider->getConfig());
    }

    /**
     * Test for method getConfig with context mock.
     *
     * @return void
     */
    public function testGetConfigWithContext()
    {
        $quoteValue = 42;
        $this->context->getRequest()->expects($this->any())->method('getParam')
            ->with('negotiableQuoteId')->willReturn($quoteValue);
        $this->quoteRepository->expects($this->any())->method('get')->willReturn($this->quote);

        $orderPayment = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethod'])
            ->getMockForAbstractClass();

        $this->quote->expects($this->any())->method('getPayment')->willReturn($orderPayment);
        $orderPayment->expects($this->any())->method('getMethod')->willReturn($quoteValue);

        $extensionAttributes = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getNegotiableQuote']
        );
        $this->quote->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $quoteNegotiation = $this->createMock(\Magento\NegotiableQuote\Model\NegotiableQuote::class);
        $extensionAttributes->expects($this->any())->method('getNegotiableQuote')->willReturn($quoteNegotiation);
        $quoteNegotiation->expects($this->once())->method('getStatus')->willReturn($quoteValue);
        $quoteNegotiation->expects($this->once())->method('getNegotiatedPriceValue')->willReturn($quoteValue);

        $this->assertArrayHasKey('isNegotiableQuote', $this->negotiableQuoteConfigProvider->getConfig());
    }
}
