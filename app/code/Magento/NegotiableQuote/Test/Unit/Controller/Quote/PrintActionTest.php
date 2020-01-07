<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Quote;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\NegotiableQuote\Controller\Quote\PrintAction;
use Magento\NegotiableQuote\Helper\Quote as QuoteHelper;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\NegotiableQuote\Model\SettingsProvider;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Tests \Magento\NegotiableQuote\Controller\Quote\PrintAction class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PrintActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PrintAction
     */
    private $controller;

    /**
     * @var QuoteHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteHelper;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var SettingsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $settingsProvider;

    /**
     * @var NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @var CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRestriction;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    private $address;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->resource = $this->createMock(RequestInterface::class);
        $this->settingsProvider = $this->createMock(SettingsProvider::class);
        $this->quoteRepository = $this->createMock(CartRepositoryInterface::class);
        $this->customerRestriction = $this->createMock(RestrictionInterface::class);
        $this->resultFactory = $this->createPartialMock(ResultFactory::class, ['create']);
        $this->redirectFactory = $this->createPartialMock(RedirectFactory::class, ['create']);
        $this->negotiableQuoteManagement = $this->getMockBuilder(NegotiableQuoteManagementInterface::class)
            ->setMethods(['prepareForOpen'])
            ->getMockForAbstractClass();
        $this->quoteHelper = $this->createPartialMock(QuoteHelper::class, ['resolveCurrentQuote']);
        $this->address = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\Quote\Address::class,
            ['updateQuoteShippingAddressDraft']
        );
        $viewAccess = $this->createMock(\Magento\NegotiableQuote\Model\Quote\ViewAccessInterface::class);
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->setMethods(['addError'])
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->controller = $objectManager->getObject(
            PrintAction::class,
            [
                'request' => $this->resource,
                'resultRedirectFactory' => $this->redirectFactory,
                'quoteHelper' => $this->quoteHelper,
                'quoteRepository' => $this->quoteRepository,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'customerRestriction' => $this->customerRestriction,
                'settingsProvider' => $this->settingsProvider,
                'negotiableQuoteAddress' => $this->address,
                'resultFactory' => $this->resultFactory,
                'viewAccess' => $viewAccess,
                'messageManager' => $this->messageManager,
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecuteAccessibleQuote(): void
    {
        $quoteId = 1;
        $quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getId']);
        $quote->expects($this->once())
            ->method('getId')
            ->willReturn($quoteId);

        $this->quoteHelper->expects($this->once())
            ->method('resolveCurrentQuote')
            ->willReturn($quote);
        $this->address->expects($this->once())
            ->method('updateQuoteShippingAddressDraft')
            ->with($quoteId);

        $block = $this->createPartialMock(\Magento\Framework\View\Element\Html\Links::class, ['setActive']);
        $block->expects($this->once())
            ->method('setActive')
            ->with('negotiable_quote/quote');

        $layout = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['getBlock']);
        $layout->expects($this->once())
            ->method('getBlock')
            ->with('customer_account_navigation')
            ->willReturn($block);

        $pageTitle = $this->createPartialMock(\Magento\Framework\View\Page\Title::class, ['set']);
        $pageConfig = $this->createPartialMock(\Magento\Framework\View\Page\Config::class, ['getTitle']);
        $page = $this->createPartialMock(
            \Magento\Framework\View\Result\Page::class,
            ['getConfig', 'getLayout']
        );
        $page->expects($this->once())->method('getConfig')->willReturn($pageConfig);
        $page->expects($this->any())->method('getLayout')->willReturn($layout);
        $pageConfig->expects($this->once())->method('getTitle')->willReturn($pageTitle);
        $pageTitle->expects($this->once())->method('set')->with(__('Quote'))->willReturnSelf();
        $this->resultFactory
            ->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_PAGE)
            ->willReturn($page);

        $result = $this->controller->execute();
        $this->assertInstanceOf(\Magento\Framework\View\Result\Page::class, $result);
    }

    /**
     * @return void
     */
    public function testExecuteNotAccessibleQuote(): void
    {
        $this->quoteHelper->expects($this->once())
            ->method('resolveCurrentQuote')
            ->willReturn(null);
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with(__('Requested quote was not found'));
        $redirect = $this->createPartialMock(\Magento\Framework\Controller\Result\Redirect::class, ['setPath']);
        $redirect->expects($this->any())->method('setPath')->willReturnSelf();
        $this->redirectFactory->expects($this->any())->method('create')->willReturn($redirect);

        $result = $this->controller->execute();
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }
}
