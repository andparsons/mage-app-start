<?php

namespace Magento\Company\Test\Unit\Plugin\Framework\App\Action;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class AbstractActionPluginTest.
 */
class AbstractActionPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\Company\Plugin\Framework\App\Action\CustomerLoginChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerLoginChecker;

    /**
     * @var \Magento\Company\Plugin\Framework\App\Action\AbstractActionPlugin
     */
    private $plugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->resultFactory = $this
            ->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilder = $this
            ->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMockForAbstractClass();
        $this->customerLoginChecker = $this
            ->getMockBuilder(\Magento\Company\Plugin\Framework\App\Action\CustomerLoginChecker::class)
            ->disableOriginalConstructor()
            ->setMethods(['isLoginAllowed'])
            ->getMockForAbstractClass();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManagerHelper->getObject(
            \Magento\Company\Plugin\Framework\App\Action\AbstractActionPlugin::class,
            [
                'customerLoginChecker' => $this->customerLoginChecker,
                'resultFactory' => $this->resultFactory,
                'urlBuilder' => $this->urlBuilder
            ]
        );
    }

    /**
     * Test aroundDispatch method.
     *
     * @return void
     */
    public function testAroundDispatch()
    {
        $subject = $this
            ->getMockBuilder(\Magento\Framework\App\Action\AbstractAction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this
            ->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isPost', 'isAjax'])
            ->getMockForAbstractClass();
        $redirect = $this
            ->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->setMethods(['setData', 'setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $proceed = function ($request) {
            return true;
        };
        $this->customerLoginChecker->expects($this->once())->method('isLoginAllowed')->willReturn(true);
        $request->expects($this->once())->method('isPost')->willReturn(true);
        $this->resultFactory->expects($this->at(0))
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($redirect);
        $redirect->expects($this->once())->method('setPath')->with('customer/account/logout')->willReturnSelf();
        $request->expects($this->once())->method('isAjax')->willReturn(true);
        $this->resultFactory->expects($this->at(1))
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($redirect);
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('customer/account/logout')
            ->willReturn('http://example.com/customer/account/logout');
        $redirect->expects($this->once())
            ->method('setData')
            ->with(['backUrl' => 'http://example.com/customer/account/logout'])
            ->willReturnSelf();
        $this->assertEquals($redirect, $this->plugin->aroundDispatch($subject, $proceed, $request));
    }
}
