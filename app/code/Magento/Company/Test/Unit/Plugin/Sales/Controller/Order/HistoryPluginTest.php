<?php

namespace Magento\Company\Test\Unit\Plugin\Sales\Controller\Order;

/**
 * Unit test for \Magento\Company\Plugin\Sales\Controller\Order\HistoryPlugin.
 */
class HistoryPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorization;

    /**
     * @var \Magento\Company\Model\CompanyContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyContext;

    /**
     * @var \Magento\Sales\Controller\Order\HistoryPlugin
     */
    private $historyPlugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->resultRedirectFactory = $this
            ->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->authorization = $this->getMockBuilder(\Magento\Company\Api\AuthorizationInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->companyContext = $this->getMockBuilder(\Magento\Company\Model\CompanyContext::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->historyPlugin = $objectManager->getObject(
            \Magento\Company\Plugin\Sales\Controller\Order\HistoryPlugin::class,
            [
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'authorization' => $this->authorization,
                'companyContext' => $this->companyContext,
            ]
        );
    }

    /**
     * Test afterExecute() method.
     *
     * @return void
     */
    public function testAfterExecute()
    {
        $controller = $this->getMockBuilder(\Magento\Sales\Controller\Order\History::class)
            ->disableOriginalConstructor()->getMock();
        $result = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()->getMock();
        $this->authorization->expects($this->once())
            ->method('isAllowed')->with('Magento_Sales::all')->willReturn(false);
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->companyContext->expects($this->once())->method('isModuleActive')->willReturn(true);
        $this->companyContext->expects($this->once())->method('isCurrentUserCompanyUser')->willReturn(true);
        $resultRedirect->expects($this->once())->method('setPath')->with('company/accessdenied')->willReturnSelf();
        $this->assertEquals($resultRedirect, $this->historyPlugin->afterExecute($controller, $result));
    }

    /**
     * Test afterExecute() method with view permissions.
     *
     * @return void
     */
    public function testAfterExecuteWithViewPermissions()
    {
        $controller = $this->getMockBuilder(\Magento\Sales\Controller\Order\History::class)
            ->disableOriginalConstructor()->getMock();
        $result = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()->getMock();
        $this->authorization->expects($this->once())
            ->method('isAllowed')->with('Magento_Sales::all')->willReturn(true);
        $this->assertEquals($result, $this->historyPlugin->afterExecute($controller, $result));
    }
}
