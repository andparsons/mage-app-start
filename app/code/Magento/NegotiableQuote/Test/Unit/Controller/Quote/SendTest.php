<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Quote;

/**
 * Unit test for Send.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SendTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Controller\Quote\Send
     */
    private $controller;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\NegotiableQuote\Model\SettingsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $settingsProvider;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteAddress;

    /**
     * @var \Magento\NegotiableQuote\Controller\FileProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileProcessor;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->resource = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $redirectFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RedirectFactory::class,
            ['create']
        );
        $redirect = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $redirect->expects($this->any())->method('setPath')->will($this->returnSelf());
        $redirectFactory->expects($this->any())->method('create')->will($this->returnValue($redirect));
        $this->resultFactory = $this->createPartialMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create']
        );
        $this->negotiableQuoteManagement = $this->createMock(
            \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class
        );
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->formKeyValidator =
            $this->createMock(\Magento\Framework\Data\Form\FormKey\Validator::class);
        $quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getCustomerId']);
        $quote->expects($this->any())->method('getCustomerId')->willReturn(1);
        $this->settingsProvider = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\SettingsProvider::class,
            ['getCurrentUserId']
        );
        $this->negotiableQuoteAddress = $this->createMock(
            \Magento\NegotiableQuote\Model\Quote\Address::class
        );
        $this->fileProcessor = $this->getMockBuilder(\Magento\NegotiableQuote\Controller\FileProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFiles'])
            ->getMock();
        $this->settingsProvider->expects($this->any())->method('getCurrentUserId')->willReturn(1);
        $this->quoteRepository->expects($this->any())->method('get')->will($this->returnValue($quote));
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Quote\Send::class,
            [
                'resultFactory' => $this->resultFactory,
                'quoteRepository' => $this->quoteRepository,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'settingsProvider' => $this->settingsProvider,
                'formKeyValidator' => $this->formKeyValidator,
                'negotiableQuoteAddress' => $this->negotiableQuoteAddress,
                '_request' => $this->resource,
                'resultRedirectFactory' => $redirectFactory,
                'fileProcessor' => $this->fileProcessor,
                'messageManager' => $this->messageManager
            ]
        );
    }

    /**
     * Test for method execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $this->resource->expects($this->at(0))
            ->method('getParam')->with('quote_id')->will($this->returnValue(1));
        $this->resource->expects($this->at(1))
            ->method('getParam')->with('comment')->will($this->returnValue('Test comment'));
        $this->formKeyValidator->expects($this->any())->method('validate')->willReturn(true);
        $this->fileProcessor->expects($this->atLeastOnce())->method('getFiles')->willReturn([]);

        $resultPage = $this->createMock(\Magento\Framework\Controller\ResultInterface::class);
        $this->resultFactory->expects($this->any())
            ->method('create')->will($this->returnValue($resultPage));
        $this->negotiableQuoteAddress->expects($this->once())->method('updateQuoteShippingAddressDraft')->with(1);
        $this->negotiableQuoteManagement->expects($this->once())->method('send')->willReturn(true);

        $result = $this->controller->execute();

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $result);
    }

    /**
     * Test for method execute without form key.
     *
     * @return void
     */
    public function testExecuteWithoutFormkey()
    {
        $this->resource->expects($this->at(0))->method('getParam')->with('quote_id')->will($this->returnValue(1));
        $result = $this->controller->execute();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }

    /**
     * Test for method execute without quote id.
     *
     * @return void
     */
    public function testExecuteWithoutQuoteId()
    {
        $this->resource->expects($this->at(0))->method('getParam')->with('quote_id')->will($this->returnValue(0));
        $result = $this->controller->execute();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }

    /**
     * Test for method execute with exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $this->fileProcessor->expects($this->atLeastOnce())->method('getFiles')->willReturn([]);
        $this->resource->expects($this->at(0))->method('getParam')->with('quote_id')->will($this->returnValue(1));
        $this->formKeyValidator->expects($this->any())->method('validate')->willReturn(true);
        $this->negotiableQuoteManagement->expects($this->any())->method('send')
            ->willThrowException(new \Exception());
        $this->messageManager->expects($this->once())->method('addError');

        $result = $this->controller->execute();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }

    /**
     * Test for method execute with localized exception.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $this->fileProcessor->expects($this->atLeastOnce())->method('getFiles')->willReturn([]);
        $this->resource->expects($this->at(0))->method('getParam')->with('quote_id')->will($this->returnValue(1));
        $this->formKeyValidator->expects($this->any())->method('validate')->willReturn(true);
        $ph = new \Magento\Framework\Phrase('test');
        $this->negotiableQuoteManagement->expects($this->any())->method('send')
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException($ph));
        $this->messageManager->expects($this->once())->method('addError');

        $result = $this->controller->execute();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }

    /**
     * Test for method execute with customer.
     *
     * @return void
     */
    public function testExecuteWithCustomer()
    {
        $this->fileProcessor->expects($this->atLeastOnce())->method('getFiles')->willReturn([]);
        $this->resource->expects($this->at(0))->method('getParam')->with('quote_id')->will($this->returnValue(1));
        $this->formKeyValidator->expects($this->any())->method('validate')->willReturn(true);
        $this->negotiableQuoteManagement->expects($this->once())->method('send')->willReturn(false);

        $result = $this->controller->execute();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }
}
