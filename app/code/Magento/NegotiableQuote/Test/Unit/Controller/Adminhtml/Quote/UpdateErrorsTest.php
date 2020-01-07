<?php
namespace Magento\NegotiableQuote\Test\Unit\Controller\Adminhtml\Quote;

/**
 * Class UpdateErrorsTest
 */
class UpdateErrorsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\NegotiableQuote\Controller\Adminhtml\Quote\UpdateErrors
     */
    private $controller;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRawFactory;

    /**
     * @inherindoc
     */
    protected function setUp()
    {
        $layout = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['renderElement'], []);
        $layout->expects($this->once())->method('renderElement')->willReturn('test');
        $resultPage = $this->createPartialMock(\Magento\Framework\View\Result\Page::class, ['addHandle', 'getLayout']);
        $resultPage->expects($this->exactly(2))
            ->method('addHandle')
            ->withConsecutive(
                ['sales_order_create_load_block_json'],
                ['quotes_quote_update_load_block_errors']
            );
        $resultPage->expects($this->once())->method('getLayout')->willReturn($layout);
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultFactory->expects($this->once())->method('create')->willReturn($resultPage);
        $this->resultRawFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RawFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRaw = $this->getMockBuilder(\Magento\Framework\Controller\Result\Raw::class)
            ->disableOriginalConstructor()
            ->setMethods(['setContents'])
            ->getMock();
        $resultRaw->expects($this->once())->method('setContents')->will($this->returnValue($resultRaw));
        $this->resultRawFactory->expects($this->once())->method('create')->will($this->returnValue($resultRaw));

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Adminhtml\Quote\UpdateErrors::class,
            [
                'resultFactory' => $this->resultFactory,
                'resultRawFactory' => $this->resultRawFactory
            ]
        );
    }

    /**
     * Test for method execute
     */
    public function testExecute()
    {
        $result = $this->controller->execute();
        get_class($result);
        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $result);
    }
}
