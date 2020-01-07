<?php

namespace Magento\CompanyCredit\Test\Unit\Controller\History;

/**
 * Class IndexTest.
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\CompanyCredit\Controller\History\Index
     */
    private $index;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->resultFactory = $this->createPartialMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create']
        );
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->index = $objectManager->getObject(
            \Magento\CompanyCredit\Controller\History\Index::class,
            [
                'resultFactory' => $this->resultFactory,
                '_request' => $this->request,
            ]
        );
    }

    /**
     * Test for method execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $resultPage = $this->createPartialMock(
            \Magento\Framework\View\Result\Page::class,
            ['getConfig', 'getLayout']
        );
        $title = $this->createMock(\Magento\Framework\View\Page\Title::class);
        $config = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $config->expects($this->once())->method('getTitle')->willReturn($title);
        $resultPage->expects($this->once())->method('getConfig')->willReturn($config);
        $title->expects($this->once())->method('set')->with(__('Company Credit'))->willReturnSelf();
        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $resultPage->expects($this->once())->method('getLayout')->willReturn($layout);
        $block = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\BlockInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['setActive']
        );
        $layout->expects($this->once())->method('getBlock')
            ->with('customer-account-navigation-company-credit-history-link')->willReturn($block);
        $block->expects($this->once())->method('setActive')->with('company_credit/history')->willReturnSelf();
        $this->resultFactory->expects($this->once())
            ->method('create')->with(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE)->willReturn($resultPage);
        $this->assertEquals($resultPage, $this->index->execute());
    }
}
