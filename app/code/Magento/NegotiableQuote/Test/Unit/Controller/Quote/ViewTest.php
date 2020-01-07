<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Quote;

use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterfaceFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Controller\Quote\View
     */
    private $controller;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourse;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRestriction;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\NegotiableQuote\Model\SettingsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $settingsProvider;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteHelper;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    /**
     * @var RestrictionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restrictionFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\ViewAccessInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $viewAccess;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->resourse = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getParam',
                'getFullActionName',
                'getRouteName',
                'isDispatched',
            ])
            ->getMockForAbstractClass();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addErrorMessage'])
            ->getMockForAbstractClass();
        $this->resultPageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourse->expects($this->any())->method('getParam')->with('quote_id')->will($this->returnValue(1));
        $this->customerRestriction = $this->getMockBuilder(RestrictionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $redirectFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $redirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $redirect->expects($this->any())
            ->method('setPath')->will($this->returnSelf());
        $redirectFactory->expects($this->any())
            ->method('create')->will($this->returnValue($redirect));
        $this->negotiableQuoteManagement = $this->getMockBuilder(NegotiableQuoteManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->settingsProvider = $this->getMockBuilder(\Magento\NegotiableQuote\Model\SettingsProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteHelper = $this->getMockBuilder(\Magento\NegotiableQuote\Helper\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->response = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->restrictionFactory = $this->getMockBuilder(RestrictionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewAccess = $this->createMock(\Magento\NegotiableQuote\Model\Quote\ViewAccessInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Quote\View::class,
            [
                '_request' => $this->resourse,
                'resultFactory' => $this->resultFactory,
                'resultPageFactory' => $this->resultPageFactory,
                'quoteRepository' => $this->quoteRepository,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'resultRedirectFactory' => $redirectFactory,
                'customerRestriction' => $this->customerRestriction,
                'storeManager' => $this->storeManager,
                'messageManager' => $this->messageManager,
                'settingsProvider' => $this->settingsProvider,
                'quoteHelper' => $this->quoteHelper,
                '_response' => $this->response,
                'restrictionFactory' => $this->restrictionFactory,
                'viewAccess' => $this->viewAccess,
            ]
        );
    }

    /**
     * Test for isAllowed() method.
     *
     * @return void
     */
    public function testIsAllowed(): void
    {
        $this->prepareMocksForIsAllowed();
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->negotiableQuoteManagement->expects($this->atLeastOnce())->method('getNegotiableQuote')
            ->willReturn($quote);
        $this->viewAccess->expects($this->once())->method('canViewQuote')->with($quote)->willReturn(true);

        $this->assertInstanceOf(
            \Magento\Framework\App\ResponseInterface::class,
            $this->controller->dispatch($this->resourse)
        );
    }

    /**
     * Test for isAllowed() method when view quote does not exist.
     *
     * @return void
     */
    public function testIsAllowedIfQuoteNotExist(): void
    {
        $this->prepareMocksForIsAllowed();
        $this->negotiableQuoteManagement->expects($this->atLeastOnce())->method('getNegotiableQuote')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->viewAccess->expects($this->never())->method('canViewQuote');

        $this->assertInstanceOf(
            \Magento\Framework\App\ResponseInterface::class,
            $this->controller->dispatch($this->resourse)
        );
    }

    /**
     * Prepare mocks for isAllowed() test.
     *
     * @return void
     */
    private function prepareMocksForIsAllowed(): void
    {
        $this->settingsProvider->expects($this->once())->method('isModuleEnabled')->willReturn(true);
        $this->settingsProvider->expects($this->once())
            ->method('getCurrentUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);
        $this->quoteHelper->expects($this->once())->method('getCurrentUserId')->willReturn(1);
        $this->quoteHelper->expects($this->once())->method('isEnabled')->willReturn(true);
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteHelper->expects($this->once())->method('resolveCurrentQuote')->willReturn($quote);

        $page = $this->createPartialMock(
            \Magento\Framework\View\Result\Page::class,
            ['getConfig', 'getLayout']
        );
        $title = $this->createPartialMock(\Magento\Framework\View\Page\Title::class, ['set']);
        $config = $this->createPartialMock(\Magento\Framework\View\Page\Config::class, ['getTitle']);
        $layout = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['getBlock']);
        $block = $this->createPartialMock(\Magento\Framework\View\Element\Html\Links::class, ['setActive']);
        $page->expects($this->atLeastOnce())->method('getConfig')->willReturn($config);
        $config->expects($this->atLeastOnce())->method('getTitle')->willReturn($title);
        $title->expects($this->atLeastOnce())->method('set')->with(__('Quote'));
        $layout->expects($this->atLeastOnce())->method('getBlock')->willReturn($block);
        $page->expects($this->atLeastOnce())->method('getLayout')->willReturn($layout);
        $this->resultFactory
            ->expects($this->atLeastOnce())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE)
            ->willReturn($page);

        $result = $this->controller->execute();
        $this->assertInstanceOf(\Magento\Framework\View\Result\Page::class, $result);
    }

    /**
     * Test for method execute with exception.
     *
     * @return void
     */
    public function testExecuteWithException(): void
    {
        $page = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfig'])
            ->getMock();
        $this->resultPageFactory->expects($this->any())->method('create')->will($this->returnValue($page));

        $this->messageManager->expects($this->once())->method('addErrorMessage')
            ->with(__('Requested quote was not found'));

        $result = $this->controller->execute();
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }
}
