<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Quote;

/**
 * Class ItemDeleteTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemDeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Controller\Quote\ItemDelete
     */
    private $controller;

    /**
     * @var \'Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourse;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

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
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\SettingsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $settingsProvider;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->resourse = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->messageManager = $this
            ->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addSuccess'])
            ->getMockForAbstractClass();
        $this->createRedirectMock();
        $this->negotiableQuoteManagement = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['updateProcessingByCustomerQuoteStatus', 'removeQuoteItem'])
            ->getMockForAbstractClass();
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->formKeyValidator =
            $this->createMock(\Magento\Framework\Data\Form\FormKey\Validator::class);
        $quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getCustomerId']);
        $this->quoteRepository->expects($this->any())->method('get')->will($this->returnValue($quote));
        $quote->expects($this->any())->method('getCustomerId')->willReturn(1);
        $this->settingsProvider =
            $this->createPartialMock(\Magento\NegotiableQuote\Model\SettingsProvider::class, ['getCurrentUserId']);
        $this->settingsProvider->expects($this->any())->method('getCurrentUserId')->willReturn(1);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Quote\ItemDelete::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'formKeyValidator' => $this->formKeyValidator,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'resultRedirectFactory' => $this->redirectFactory,
                '_request' => $this->resourse,
                'messageManager' => $this->messageManager,
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
        $this->resourse->expects($this->at(0))
            ->method('getParam')->with('quote_id')->will($this->returnValue(1));
        $this->resourse->expects($this->at(1))
            ->method('getParam')->with('quote_item_id')->will($this->returnValue(1));
        $this->negotiableQuoteManagement->expects($this->any())->method('removeQuoteItem');
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
        $this->resourse->expects($this->at(0))
            ->method('getParam')->with('quote_id')->will($this->returnValue(1));
        $this->resourse->expects($this->at(1))
            ->method('getParam')->with('quote_item_id')->will($this->returnValue(1));

        $this->negotiableQuoteManagement->expects($this->any())->method('removeQuoteItem')
            ->willThrowException(new \Exception());
        $this->messageManager->expects($this->never())->method('addSuccess');
        $this->messageManager->expects($this->once())->method('addException');

        $result = $this->controller->execute();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }

    /**
     * Test for mthod execute with localized exception
     */
    public function testExecuteWithLocalizedException()
    {
        $this->formKeyValidator->expects($this->any())->method('validate')->willReturn(true);
        $this->resourse->expects($this->at(0))
            ->method('getParam')->with('quote_id')->will($this->returnValue(1));
        $this->resourse->expects($this->at(1))
            ->method('getParam')->with('quote_item_id')->will($this->returnValue(1));

        $ph = new \Magento\Framework\Phrase('test');
        $this->negotiableQuoteManagement->expects($this->any())->method('removeQuoteItem')
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException($ph));
        $this->messageManager->expects($this->never())->method('addSuccess');
        $this->messageManager->expects($this->any())->method('addError');

        $result = $this->controller->execute();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }

    /**
     * @return void
     */
    private function createRedirectMock()
    {
        $this->redirectFactory =
            $this->createPartialMock(\Magento\Framework\Controller\Result\RedirectFactory::class, ['create']);
        $redirect = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $redirect->expects($this->any())->method('setPath')->will($this->returnSelf());
        $this->redirectFactory->expects($this->any())->method('create')->will($this->returnValue($redirect));
    }
}
