<?php
namespace Magento\NegotiableQuote\Test\Unit\Controller\Quote;

/**
 * Unit test for Create.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Controller\Quote\Create
     */
    private $controller;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\CommentManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentManagement;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSession;

    /**
     * @var \Magento\NegotiableQuote\Model\SettingsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $settingsProvider;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

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
        $this->resource = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->settingsProvider = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\SettingsProvider::class,
            ['retrieveJsonError', 'retrieveJsonSuccess']
        );
        $this->fileProcessor = $this->getMockBuilder(\Magento\NegotiableQuote\Controller\FileProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFiles'])
            ->getMock();
        $this->fileProcessor->expects($this->atLeastOnce())->method('getFiles')->willReturn(
            $this->getAttachmentFields()
        );
        $this->checkoutSession = $this->createMock(\Magento\Checkout\Model\Session::class);
        $config = $this->createMock(\Magento\NegotiableQuote\Helper\Config::class);
        $this->resultJsonFactory =
            $this->createPartialMock(\Magento\Framework\Controller\Result\JsonFactory::class, ['create']);
        $this->negotiableQuoteManagement = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->quote = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getData', 'getShippingAddress', 'getBillingAddress', 'getItemsCollection', 'removeAddress']
        );
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->quoteRepository->expects($this->atLeastOnce())->method('get')->willReturn($this->quote);
        $this->commentManagement = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\CommentManagementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'getFilesNamesList'])
            ->getMockForAbstractClass();
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Quote\Create::class,
            [
                'resultJsonFactory' => $this->resultJsonFactory,
                'quoteRepository' => $this->quoteRepository,
                'configHelper' => $config,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'checkoutSession' => $this->checkoutSession,
                'commentManagement' => $this->commentManagement,
                '_request' => $this->resource,
                'settingsProvider' => $this->settingsProvider,
                'logger' => $this->logger,
                'fileProcessor' => $this->fileProcessor,
                'messageManager' => $this->messageManager
            ]
        );
    }

    /**
     * Test execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $this->prepareRequestParams();
        $this->prepareSettingsProvider();
        $this->checkoutSession->expects($this->atLeastOnce())->method('clearQuote')->willReturnSelf();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->controller->execute());
    }

    /**
     * Test execute with Billing Address.
     *
     * @return void
     */
    public function testExecuteWithBillingAddress()
    {
        $this->prepareRequestParams();
        $this->quote->expects($this->atLeastOnce())->method('removeAddress')->with(5)->willReturn(true);
        $this->quote->expects($this->exactly(3))
            ->method('getBillingAddress')
            ->willReturnOnConsecutiveCalls(true, $this->getAddress(), false, true);
        $this->prepareSettingsProvider();
        $this->checkoutSession->expects($this->atLeastOnce())->method('clearQuote')->willReturnSelf();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->controller->execute());
    }

    /**
     * Test execute with Shipping Address.
     *
     * @return void
     */
    public function testExecuteWithShippingAddress()
    {
        $this->prepareRequestParams();
        $this->quote->expects($this->atLeastOnce())->method('removeAddress')->with(5)->willReturn(true);
        $this->quote->expects($this->exactly(3))
            ->method('getShippingAddress')
            ->willReturnOnConsecutiveCalls(true, $this->getAddress(), false, true);
        $this->prepareSettingsProvider();
        $this->checkoutSession->expects($this->atLeastOnce())->method('clearQuote')->willReturnSelf();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->controller->execute());
    }

    /**
     * Test execute with Shipping Address.
     *
     * @return void
     */
    public function testExecuteWithExtensionAttributes()
    {
        $this->prepareRequestParams();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShippingAssignments', 'setShippingAssignments'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->once())->method('getShippingAssignments')->willReturn(true);
        $extensionAttributes->expects($this->once())->method('setShippingAssignments')->willReturn(true);
        $this->quote->expects($this->exactly(3))->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $this->prepareSettingsProvider();
        $this->checkoutSession->expects($this->atLeastOnce())->method('clearQuote')->willReturnSelf();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->controller->execute());
    }

    /**
     * Test execute with Exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $this->prepareRequestParams();
        $this->prepareSettingsProvider();
        $exception = new \Exception();
        $this->negotiableQuoteManagement->expects($this->once())->method('create')->willThrowException($exception);
        $this->messageManager->expects($this->once())->method('addExceptionMessage');

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->controller->execute());
    }

    /**
     * Test execute with LocalizedException.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $this->prepareRequestParams();
        $this->prepareSettingsProvider();
        $phrase = new \Magento\Framework\Phrase(__('Exception'));
        $exception = new \Magento\Framework\Exception\LocalizedException($phrase);
        $this->negotiableQuoteManagement->expects($this->once())->method('create')->willThrowException($exception);
        $this->messageManager->expects($this->once())->method('addErrorMessage');

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->controller->execute());
    }

    /**
     * Get attachment fields.
     *
     * @return array
     */
    private function getAttachmentFields()
    {
        return [
            0 => [
                'name' => 'product.png',
                'type' => 'image/png',
                'tmp_name' => '/tmp/phpN5Ikxl',
                'error' => 0,
                'size' => 20141
            ],
            1 => [
                'name' => 'box1.png',
                'type' => 'image/png',
                'tmp_name' => '/tmp/php8QspRg',
                'error' => 0,
                'size' => 118561
            ]
        ];
    }

    /**
     * Prepare request params.
     *
     * @return void
     */
    private function prepareRequestParams()
    {
        $this->resource->expects($this->exactly(3))
            ->method('getParam')
            ->withConsecutive(['quote-name'], ['quote-message'], ['quote_id'])
            ->willReturnOnConsecutiveCalls('Test Quote', 'Test comment', 1);
    }

    /**
     * Prepare settings provider.
     *
     * @return void
     */
    private function prepareSettingsProvider()
    {
        $resultJson = $this->createPartialMock(\Magento\Framework\Controller\Result\Json::class, ['setData'], []);
        $this->settingsProvider->expects($this->any())->method('retrieveJsonError')->willReturn($resultJson);
        $this->settingsProvider->expects($this->any())->method('retrieveJsonSuccess')->willReturn($resultJson);
    }

    /**
     *  Create address with interface \Magento\Quote\Api\Data\AddressInterface.
     *
     * @return \Magento\Quote\Api\Data\AddressInterface  Cart billing/shipping address.
     */
    private function getAddress()
    {
        $address = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $address->expects($this->once())->method('getId')->willReturn(5);
        return $address;
    }
}
