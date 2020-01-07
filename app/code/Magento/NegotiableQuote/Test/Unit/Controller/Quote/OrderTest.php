<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Quote;

/**
 * Class OrderTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Controller\Quote\Order
     */
    private $controller;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageFactory;

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
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\NegotiableQuote\Model\SettingsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $settingsProvider;

    /**
     * Set up
     */
    protected function setUp()
    {
        $resource = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $resource->expects($this->at(0))
            ->method('getParam')->with('quote_id')->will($this->returnValue(1));
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $redirectFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RedirectFactory::class,
            ['create']
        );
        $redirect = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $redirect->expects($this->any())->method('setPath')->will($this->returnSelf());
        $redirectFactory->expects($this->any())->method('create')->will($this->returnValue($redirect));
        $this->resultPageFactory = $this->createPartialMock(
            \Magento\Framework\View\Result\PageFactory::class,
            ['create']
        );
        $this->negotiableQuoteManagement = $this->createMock(
            \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class
        );
        $this->settingsProvider = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\SettingsProvider::class,
            ['getCurrentUserId']
        );
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->formKeyValidator =
            $this->createMock(\Magento\Framework\Data\Form\FormKey\Validator::class);
        $quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getCustomerId']);
        $this->settingsProvider->expects($this->any())->method('getCurrentUserId')->willReturn(1);
        $quote->expects($this->any())->method('getCustomerId')->willReturn(1);
        $this->quoteRepository->expects($this->any())->method('get')->will($this->returnValue($quote));
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Quote\Order::class,
            [
                'resultPageFactory' => $this->resultPageFactory,
                'quoteRepository' => $this->quoteRepository,
                'formKeyValidator' => $this->formKeyValidator,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'messageManager' => $this->messageManager,
                'resultRedirectFactory' => $redirectFactory,
                '_request' => $resource,
                'settingsProvider' => $this->settingsProvider
            ]
        );
    }

    /**
     * Test for method execute
     */
    public function testExecute()
    {
        $this->formKeyValidator->expects($this->any())->method('validate')->willReturn(true);

        $resultPage = $this->createMock(\Magento\Framework\View\Result\Page::class);
        $this->resultPageFactory->expects($this->any())
            ->method('create')->will($this->returnValue($resultPage));
        $this->negotiableQuoteManagement->expects($this->once())->method('order')->willReturn(true);
        $this->messageManager->expects($this->any())->method('addSuccess');

        $result = $this->controller->execute();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }

    /**
     * Test for method execute without form key
     */
    public function testExecuteWithoutFormkey()
    {
        $result = $this->controller->execute();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }

    /**
     * Test for method execute with exception
     */
    public function testExecuteWithException()
    {
        $this->formKeyValidator->expects($this->any())->method('validate')->willReturn(true);
        $this->negotiableQuoteManagement->expects($this->once())->method('order')
            ->willThrowException(new \Exception());
        $this->messageManager->expects($this->once())->method('addError');

        $result = $this->controller->execute();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }

    /**
     * Test for method execute with localized exception
     */
    public function testExecuteWithLocalizedException()
    {
        $this->formKeyValidator->expects($this->any())->method('validate')->willReturn(true);
        $ph = new \Magento\Framework\Phrase('test');
        $this->negotiableQuoteManagement->expects($this->once())->method('order')
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException($ph));
        $this->messageManager->expects($this->once())->method('addError');

        $result = $this->controller->execute();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }

    /**
     * Test for method execute with customer
     */
    public function testExecuteWithCustomer()
    {
        $this->formKeyValidator->expects($this->any())->method('validate')->willReturn(true);
        $this->negotiableQuoteManagement->expects($this->once())->method('order')->willReturn(false);

        $result = $this->controller->execute();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }
}
