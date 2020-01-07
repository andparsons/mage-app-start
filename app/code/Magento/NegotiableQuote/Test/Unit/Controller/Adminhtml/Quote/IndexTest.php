<?php
namespace Magento\NegotiableQuote\Test\Unit\Controller\Adminhtml\Quote;

class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Controller\Adminhtml\Quote\Index
     */
    private $controller;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Adminhtml\Quote\Index::class,
            [
                'resultFactory' => $this->resultFactory,
                'logger' => $logger,
                'quoteRepository' => $this->quoteRepository,
            ]
        );
    }

    public function testExecute()
    {
        $page = $this->createPartialMock(
            \Magento\Framework\View\Result\Page::class,
            ['setActiveMenu', 'addBreadcrumb', 'getConfig']
        );
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->will($this->returnValue($page));
        $title = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()->getMock();
        $config = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()->getMock();
        $page->expects($this->any())->method('getConfig')->will($this->returnValue($config));
        $config->expects($this->any())->method('getTitle')->will($this->returnValue($title));
        $result = $this->controller->execute();
        $this->assertInstanceOf(\Magento\Framework\View\Result\Page::class, $result);
    }
}
