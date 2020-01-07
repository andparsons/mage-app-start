<?php
namespace Magento\QuickOrder\Test\Unit\Controller\Index;

/**
 * Test for \Magento\QuickOrder\Controller\Index\Index class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\QuickOrder\Controller\Index\Index
     */
    private $controller;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirect;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageFactory;

    /**
     * @var \Magento\QuickOrder\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleConfig;

    /**
     * @var \Magento\AdvancedCheckout\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            false,
            false,
            ['getFullActionName', 'getRouteName', 'isDispatched']
        );

        $response = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->getMockForAbstractClass();

        $this->redirect = $this->getMockBuilder(\Magento\Framework\App\Response\RedirectInterface::class)
            ->getMockForAbstractClass();

        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $this->helper = $this->createMock(\Magento\AdvancedCheckout\Helper\Data::class);
        $this->objectManager->expects($this->any())->method('get')->willReturn($this->helper);

        $eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->resultPageFactory =
            $this->createPartialMock(\Magento\Framework\View\Result\PageFactory::class, ['create']);
        $this->moduleConfig = $this->createMock(\Magento\QuickOrder\Model\Config::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\QuickOrder\Controller\Index\Index::class,
            [
                'moduleConfig' => $this->moduleConfig,
                'resultPageFactory' => $this->resultPageFactory,
                '_redirect' => $this->redirect,
                '_request' => $this->request,
                '_response' => $response,
                '_eventManager' => $eventManager,
                '_objectManager' => $this->objectManager,
            ]
        );
    }

    /**
     * Test for execute() method.
     *
     * @return void
     */
    public function testExecute()
    {
        $page = $this->createMock(\Magento\Framework\View\Result\Page::class);
        $this->resultPageFactory->expects($this->any())->method('create')->will($this->returnValue($page));

        $config = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $page->expects($this->once())->method('getConfig')->willReturn($config);

        $title = $this->createPartialMock(\Magento\Framework\View\Page\Title::class, ['set'], []);
        $config->expects($this->once())->method('getTitle')->willReturn($title);

        $this->assertEquals($page, $this->controller->execute());
    }

    /**
     * Test for dispatch() method.
     *
     * @param bool $isSkuEnabled
     * @param bool $isModuleActive
     * @param bool $isRedirectExpected
     * @return void
     * @dataProvider dispatchDataProvider
     */
    public function testDispatch($isSkuEnabled, $isModuleActive, $isRedirectExpected)
    {
        $this->helper->expects($this->any())->method('isSkuEnabled')->willReturn($isSkuEnabled);
        $this->helper->expects($this->any())->method('isSkuApplied')->willReturn($isSkuEnabled);
        $this->moduleConfig->expects($this->any())->method('isActive')->willReturn($isModuleActive);
        $this->redirect->expects($isRedirectExpected ? $this->once() : $this->never())->method('redirect');

        $this->assertInstanceOf(
            \Magento\Framework\App\ResponseInterface::class,
            $this->controller->dispatch($this->request)
        );
    }

    /**
     * Test for dispatch() method with exception.
     *
     * @expectedException \Magento\Framework\Exception\NotFoundException
     * @return void
     */
    public function testDispatchWithException()
    {
        $this->helper->expects($this->any())->method('isSkuEnabled')->willReturn(true);
        $this->helper->expects($this->any())->method('isSkuApplied')->willReturn(true);
        $this->moduleConfig->expects($this->any())->method('isActive')->willReturn(false);
        $this->redirect->expects($this->never())->method('redirect');

        $this->controller->dispatch($this->request);
    }

    /**
     * Data provider dispatch.
     *
     * @return array
     */
    public function dispatchDataProvider()
    {
        return [
            [false, true, true],
            [true, true, false],
        ];
    }
}
