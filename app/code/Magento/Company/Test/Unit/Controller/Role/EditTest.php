<?php

namespace Magento\Company\Test\Unit\Controller\Role;

/**
 * Class EditTest.
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $view;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Company\Controller\Role\Edit
     */
    private $controller;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->view = $this->getMockBuilder(\Magento\Framework\App\ViewInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadLayout', 'loadLayoutUpdates', 'getPage', 'renderLayout'])
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\Company\Controller\Role\Edit::class,
            [
                '_view' => $this->view,
                '_request' => $this->request,
            ]
        );
    }

    /**
     * Test execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $phrase = new \Magento\Framework\Phrase('Add New Role');
        $editRolePhrase = new \Magento\Framework\Phrase('Edit Role');
        $resultPage = $this->createPartialMock(
            \Magento\Framework\View\Result\Page::class,
            ['getConfig']
        );
        $resultConfig = $this->createPartialMock(
            \Magento\Framework\View\Result\Page::class,
            ['getTitle']
        );
        $resultTitle = $this->createPartialMock(
            \Magento\Framework\View\Page\Title::class,
            ['set']
        );
        $this->view->expects($this->once())
            ->method('loadLayout')
            ->willReturnSelf();
        $this->view->expects($this->once())
            ->method('loadLayoutUpdates')
            ->willReturnSelf();
        $this->request->expects($this->once())->method('getParam')->with('id')->willReturn(1);
        $this->view->expects($this->exactly(2))
            ->method('getPage')
            ->willReturn($resultPage);
        $resultPage->expects($this->exactly(2))->method('getConfig')->willReturn($resultConfig);
        $resultConfig->expects($this->exactly(2))->method('getTitle')->willReturn($resultTitle);
        $resultTitle->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive([$phrase], [$editRolePhrase])
            ->willReturnSelf();
        $this->view->expects($this->once())
            ->method('renderLayout')
            ->willReturnSelf();

        $this->controller->execute();
    }
}
