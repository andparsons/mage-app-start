<?php

namespace Magento\Company\Test\Unit\Controller\Index;

/**
 * Class IndexTest.
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit\Framework\MockObject_MockObject
     */
    private $view;

    /**
     * @var \Magento\Company\Controller\Index\Index
     */
    private $index;

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
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->index = $objectManager->getObject(
            \Magento\Company\Controller\Index\Index::class,
            [
                '_view' => $this->view,
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
        $phrase = new \Magento\Framework\Phrase('Company Structure');
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
        $this->view->expects($this->once())
            ->method('getPage')
            ->willReturn($resultPage);
        $resultPage->expects($this->once())->method('getConfig')->willReturn($resultConfig);
        $resultConfig->expects($this->once())->method('getTitle')->willReturn($resultTitle);
        $resultTitle->expects($this->once())->method('set')->with($phrase)->willReturnSelf();
        $this->view->expects($this->once())
            ->method('renderLayout')
            ->willReturnSelf();

        $this->index->execute();
    }
}
