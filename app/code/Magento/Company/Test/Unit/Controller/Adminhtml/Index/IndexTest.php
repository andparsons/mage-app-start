<?php

namespace Magento\Company\Test\Unit\Controller\Adminhtml\Index;

/**
 * Class IndexTest.
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Company\Controller\Adminhtml\Index\Index
     */
    private $index;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->resultPageFactory = $this->createPartialMock(
            \Magento\Framework\View\Result\PageFactory::class,
            ['create']
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->index = $objectManager->getObject(
            \Magento\Company\Controller\Adminhtml\Index\Index::class,
            [
                'resultPageFactory' => $this->resultPageFactory
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
        $phrase = new \Magento\Framework\Phrase('Companies');
        $resultPage = $this->createPartialMock(
            \Magento\Backend\Model\View\Result\Page::class,
            ['getConfig', 'setActiveMenu', 'addBreadcrumb']
        );
        $resultConfig = $this->createPartialMock(
            \Magento\Framework\View\Result\Page::class,
            ['getTitle']
        );
        $resultTitle = $this->createPartialMock(
            \Magento\Framework\View\Page\Title::class,
            ['prepend']
        );
        $this->resultPageFactory->expects($this->once())->method('create')->willReturn($resultPage);
        $resultPage->expects($this->once())
            ->method('setActiveMenu')
            ->with('Magento_Company::company_index')
            ->willReturnSelf();
        $resultPage->expects($this->once())->method('getConfig')->willReturn($resultConfig);
        $resultConfig->expects($this->once())->method('getTitle')->willReturn($resultTitle);
        $resultTitle->expects($this->once())->method('prepend')->with($phrase)->willReturnSelf();
        $resultPage->expects($this->once())
            ->method('addBreadcrumb')
            ->with($phrase, $phrase)
            ->willReturnSelf();

        $this->assertEquals($resultPage, $this->index->execute());
    }
}
