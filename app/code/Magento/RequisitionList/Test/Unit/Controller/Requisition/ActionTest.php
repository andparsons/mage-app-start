<?php

namespace Magento\RequisitionList\Test\Unit\Controller\Requisition;

/**
 * Class ActionTest
 */
abstract class ActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Model\Action\RequestValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestValidator;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /**
     * @var \Magento\RequisitionList\Controller\Requisition\Index
     */
    protected $mock;

    /**
     * @var string
     */
    protected $mockClass;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->requestValidator = $this->createMock(\Magento\RequisitionList\Model\Action\RequestValidator::class);
        $this->resultFactory =
            $this->createPartialMock(\Magento\Framework\Controller\ResultFactory::class, ['create'], []);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->mock = $objectManager->getObject(
            'Magento\RequisitionList\Controller\Requisition\\' . $this->mockClass,
            [
                'resultFactory' => $this->resultFactory,
                'requestValidator' => $this->requestValidator
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $this->requestValidator->expects($this->any())->method('getResult')->willReturn(null);
        $resultPage = $this->createMock(\Magento\Backend\Model\View\Result\Page::class);
        $title = $this->createPartialMock(\Magento\Framework\View\Page\Title::class, ['set'], []);
        $title->expects($this->any())->method('set')->willReturnSelf();
        $pageConfig = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $pageConfig->expects($this->any())->method('getTitle')->willReturn($title);
        $layout = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['getBlock', 'setActive'], []);
        $layout->expects($this->atLeastOnce())->method('getBlock')->willReturn($layout);
        $layout->expects($this->once())->method('setActive')->willReturnSelf();
        $resultPage->expects($this->any())->method('getConfig')->willReturn($pageConfig);
        $resultPage->expects($this->any())->method('getLayout')->willReturn($layout);
        $this->resultFactory->expects($this->any())->method('create')->willReturn($resultPage);

        $this->assertInstanceOf(\Magento\Backend\Model\View\Result\Page::class, $this->mock->execute());
    }

    /**
     * Test execute method not allowed action
     */
    public function testExecuteWithNotAllowedAction()
    {
        $resultRedirect = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $this->resultFactory->expects($this->any())->method('create')->willReturn($resultRedirect);
        $this->requestValidator->expects($this->any())->method('getResult')->willReturn($resultRedirect);

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $this->mock->execute());
    }
}
