<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Email;

/**
 * Unit test for Sender.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transportBuilder;

    /**
     * @var \Magento\NegotiableQuote\Model\Email\RecipientFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $recipientFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Email\LinkBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $linkBuilder;

    /**
     * @var \Magento\NegotiableQuote\Model\Email\Provider\SalesRepresentative|\PHPUnit_Framework_MockObject_MockObject
     */
    private $salesRepresentativeProvider;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailData;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Email\Sender
     */
    private $sender;

    /**
     * Set up.
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $user = $this->getMockBuilder(\Magento\User\Api\Data\UserInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getName'])
            ->getMockForAbstractClass();
        $user->expects($this->any())->method('load')->will($this->returnSelf());
        $user->expects($this->any())->method('getName')->will($this->returnValue('Name'));
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $transport = $this->getMockBuilder(\Magento\Framework\Mail\TransportInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['sendMessage'])
            ->getMockForAbstractClass();
        $this->transportBuilder = $this->getMockBuilder(\Magento\Framework\Mail\Template\TransportBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setTemplateIdentifier',
                    'setTemplateOptions',
                    'setTemplateVars',
                    'setFrom',
                    'setReplyTo',
                    'addTo',
                    'addBcc',
                    'getTransport'
                ]
            )
            ->getMock();
        $this->transportBuilder->expects($this->any())->method('setTemplateIdentifier')->will($this->returnSelf());
        $this->transportBuilder->expects($this->any())->method('setTemplateOptions')->will($this->returnSelf());
        $this->transportBuilder->expects($this->any())->method('setTemplateVars')->will($this->returnSelf());
        $this->transportBuilder->expects($this->any())->method('setFrom')->will($this->returnSelf());
        $this->transportBuilder->expects($this->any())->method('setReplyTo')->will($this->returnSelf());
        $this->transportBuilder->expects($this->any())->method('addTo')->will($this->returnSelf());
        $this->transportBuilder->expects($this->any())->method('addBcc')->will($this->returnSelf());
        $this->transportBuilder->expects($this->any())->method('addBcc')->will($this->returnSelf());
        $this->transportBuilder->expects($this->any())->method('getTransport')->will($this->returnValue($transport));
        $this->recipientFactory = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Email\RecipientFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['createForQuote'])
            ->getMock();
        $this->linkBuilder = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Email\LinkBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->salesRepresentativeProvider = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Email\Provider\SalesRepresentative::class)
            ->disableOriginalConstructor()
            ->getMock();
        $transport->expects($this->any())->method('sendMessage')->will($this->returnSelf());
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->emailData = null;
        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomer', 'getExtensionAttributes'])
            ->getMockForAbstractClass();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->sender = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Email\Sender::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'storeManager' => $this->storeManager,
                'transportBuilder' => $this->transportBuilder,
                'recipientFactory' => $this->recipientFactory,
                'linkBuilder' => $this->linkBuilder,
                'salesRepresentativeProvider' => $this->salesRepresentativeProvider,
                'logger' => $this->logger,
                'emailData' => $this->emailData,
            ]
        );
    }

    /**
     * Test for method sendChangeQuoteEmailToMerchant.
     *
     * @return void
     */
    public function testSendChangeQuoteEmailToMerchant()
    {
        $emailTemplate = 'Email template';
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId', 'getId'])
            ->getMockForAbstractClass();
        $customer->expects($this->any())->method('getStoreId')->will($this->returnValue(1));
        $customer->expects($this->any())->method('getId')->will($this->returnValue(14));
        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $company->expects($this->any())->method('getSalesRepresentativeId')->will($this->returnValue(14));
        $this->quoteMock->expects($this->any())->method('getCustomer')
            ->will($this->returnValue($customer));
        $quoteNegotiation = $this->createMock(\Magento\NegotiableQuote\Model\NegotiableQuote::class);
        $quoteNegotiation->expects($this->any())->method('getQuoteName')->will($this->returnValue(''));
        $extensionAttributes = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getNegotiableQuote']
        );
        $extensionAttributes->expects($this->any())->method('getNegotiableQuote')
            ->will($this->returnValue($quoteNegotiation));
        $this->quoteMock->expects($this->any())->method('getExtensionAttributes')
            ->will($this->returnValue($extensionAttributes));
        $this->emailData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId'])
            ->getMock();
        $this->emailData->expects($this->any())->method('getStoreId')
            ->will($this->returnValue(1));
        $this->recipientFactory->expects($this->atLeastOnce())->method('createForQuote')->willReturn($this->emailData);
        $this->scopeConfig->expects($this->any())->method('getValue')
            ->will($this->returnValue(true));

        $this->sender->sendChangeQuoteEmailToMerchant($this->quoteMock, $emailTemplate);
    }

    /**
     * Test sendChangeQuoteEmailToMerchant with Exception.
     *
     * @return void
     */
    public function testSendChangeQuoteEmailToMerchantWithException()
    {
        $emailTemplate = 'Email template';
        $exception = new \Exception();
        $this->emailData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId'])
            ->getMock();
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->emailData->expects($this->any())->method('getStoreId')->willThrowException($exception);
        $this->recipientFactory->expects($this->atLeastOnce())->method('createForQuote')->willReturn($this->emailData);
        $this->quoteMock->expects($this->any())->method('getCustomer')->willReturn($customer);
        $this->logger->expects($this->once())->method('critical');

        $this->sender->sendChangeQuoteEmailToMerchant($this->quoteMock, $emailTemplate);
    }

    /**
     * Test for method sendChangeQuoteEmailToBuyer.
     *
     * @param int $storeId
     * @return void
     * @dataProvider dataProviderSendChangeQuoteEmailToBuyer
     */
    public function testSendChangeQuoteEmailToBuyer($storeId)
    {
        $emailTemplate = 'Email template';
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));
        $customer->expects($this->any())->method('getId')->will($this->returnValue(14));
        $this->quoteMock->expects($this->any())->method('getCustomer')
            ->will($this->returnValue($customer));
        $this->emailData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId'])
            ->getMock();
        $this->emailData->expects($this->any())->method('getStoreId')
            ->will($this->returnValue(1));
        $this->recipientFactory->expects($this->atLeastOnce())->method('createForQuote')->willReturn($this->emailData);
        $this->scopeConfig->expects($this->any())->method('getValue')
            ->will($this->returnValue(true));
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $store->expects($this->any())->method('getCode')->willReturn(1);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->sender->sendChangeQuoteEmailToBuyer($this->quoteMock, $emailTemplate);
    }

    /**
     * Test sendChangeQuoteEmailToBuyer with Exception.
     *
     * @return void
     */
    public function testSendChangeQuoteEmailToBuyerWithException()
    {
        $emailTemplate = 'Email template';
        $exception = new \Exception();
        $this->emailData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId'])
            ->getMock();
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->emailData->expects($this->any())->method('getStoreId')->willThrowException($exception);
        $this->quoteMock->expects($this->any())->method('getCustomer')->willReturn($customer);
        $this->recipientFactory->expects($this->atLeastOnce())->method('createForQuote')->willReturn($this->emailData);
        $this->logger->expects($this->once())->method('critical');

        $this->sender->sendChangeQuoteEmailToBuyer($this->quoteMock, $emailTemplate);
    }

    /**
     * DataProvider SendChangeQuoteEmailToBuyer.
     *
     * @return array
     */
    public function dataProviderSendChangeQuoteEmailToBuyer()
    {
        return [
            [1],
            [0]
        ];
    }
}
