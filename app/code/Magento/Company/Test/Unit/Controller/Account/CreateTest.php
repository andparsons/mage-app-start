<?php

namespace Magento\Company\Test\Unit\Controller\Account;

/**
 * Class CreateTest
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit\Framework\MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Company\Controller\Account\Create
     */
    private $create;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->resultFactory = $this->createMock(\Magento\Framework\Controller\ResultFactory::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->create = $objectManager->getObject(
            \Magento\Company\Controller\Account\Create::class,
            [
                'resultFactory' => $this->resultFactory,
            ]
        );
    }

    /**
     * Test for method execute
     */
    public function testExecute()
    {
        $resultPage = $this->createPartialMock(
            \Magento\Framework\View\Result\Page::class,
            ['getConfig']
        );
        $title = $this->createMock(\Magento\Framework\View\Page\Title::class);
        $config = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $config->expects($this->once())->method('getTitle')->willReturn($title);
        $resultPage->expects($this->once())->method('getConfig')->willReturn($config);
        $this->resultFactory->expects($this->once())->method('create')->willReturn($resultPage);
        $this->assertEquals($resultPage, $this->create->execute());
    }
}
