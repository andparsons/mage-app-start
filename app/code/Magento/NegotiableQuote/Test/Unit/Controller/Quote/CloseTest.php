<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Quote;

/**
 * Class CloseTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CloseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Controller\Quote\Close
     */
    private $controller;

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
     * @var \Magento\NegotiableQuote\Model\SettingsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $settingsProvider;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $redirectFactory =
            $this->createPartialMock(\Magento\Framework\Controller\Result\RedirectFactory::class, ['create']);
        $redirect = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $redirect->expects($this->any())->method('setPath')->will($this->returnSelf());
        $redirectFactory->expects($this->any())->method('create')->will($this->returnValue($redirect));
        $this->negotiableQuoteManagement =
            $this->createMock(\Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class);
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->formKeyValidator =
            $this->createMock(\Magento\Framework\Data\Form\FormKey\Validator::class);
        $quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getCustomerId'], []);
        $this->quoteRepository->expects($this->any())->method('get')->will($this->returnValue($quote));
        $quote->expects($this->any())->method('getCustomerId')->willReturn(1);
        $this->settingsProvider =
            $this->createPartialMock(\Magento\NegotiableQuote\Model\SettingsProvider::class, ['getCurrentUserId']);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Quote\Close::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'formKeyValidator' => $this->formKeyValidator,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'messageManager' => $this->messageManager,
                'resultRedirectFactory' => $redirectFactory,
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
        $this->settingsProvider->expects($this->once())->method('getCurrentUserId')->willReturn(1);
        $this->negotiableQuoteManagement->expects($this->once())->method('close')->willReturn(true);
        $this->messageManager->expects($this->once())->method('addSuccess');

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
        $this->settingsProvider->expects($this->once())->method('getCurrentUserId')->willReturn(1);
        $this->negotiableQuoteManagement->expects($this->any())->method('close')
            ->willThrowException(new \Exception());
        $this->messageManager->expects($this->never())->method('addSuccess');
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
        $this->settingsProvider->expects($this->once())->method('getCurrentUserId')->willReturn(1);
        $ph = new \Magento\Framework\Phrase('test');
        $this->negotiableQuoteManagement->expects($this->any())->method('close')
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException($ph));
        $this->messageManager->expects($this->never())->method('addSuccess');
        $this->messageManager->expects($this->once())->method('addError');

        $result = $this->controller->execute();
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }
}
