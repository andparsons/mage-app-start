<?php

namespace Magento\Company\Test\Unit\Controller\Structure;

/**
 * Class GetTest.
 */
class GetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Controller\Structure\Get
     */
    protected $get;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJson;

    /**
     * Set up
     */
    protected function setUp()
    {
        $resultJsonFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\JsonFactory::class,
            ['create']
        );
        $this->resultJson = $this->createPartialMock(
            \Magento\Framework\Controller\Result\Json::class,
            ['setData']
        );
        $resultJsonFactory->expects($this->any())
            ->method('create')->will($this->returnValue($this->resultJson));

        $layoutFactory = $this->createPartialMock(
            \Magento\Framework\View\LayoutFactory::class,
            ['create']
        );
        $layout = $this->createPartialMock(
            \Magento\Framework\View\Layout::class,
            ['createBlock']
        );
        $block = $this->createMock(
            \Magento\Company\Block\Company\Management::class
        );
        $structureManager = $this->createMock(
            \Magento\Company\Model\Company\Structure::class
        );
        $layoutFactory->expects($this->any())
            ->method('create')->will($this->returnValue($layout));
        $layout->expects($this->any())
            ->method('createBlock')->will($this->returnValue($block));
        $block->expects($this->once())->method('getTree')->willReturn([]);

        $logger = $this->createMock(
            \Psr\Log\LoggerInterface::class
        );

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->get = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Structure\Get::class,
            [
                'resultJsonFactory' => $resultJsonFactory,
                'layoutFactory' => $layoutFactory,
                'structureManager' => $structureManager,
                'logger' => $logger
            ]
        );
    }

    /**
     * Test execute
     */
    public function testExecute()
    {
        $result = '';
        $setDataCallback = function ($data) use (&$result) {
            $result = $data['status'];
        };
        $this->resultJson->expects($this->any())->method('setData')->will($this->returnCallback($setDataCallback));
        $this->get->execute();
    }
}
