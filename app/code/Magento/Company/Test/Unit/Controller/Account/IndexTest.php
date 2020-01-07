<?php

namespace Magento\Company\Test\Unit\Controller\Account;

/**
 * Class IndexTest
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Controller\ResultFactory |\PHPUnit\Framework\MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Company\Controller\Account\Index
     */
    private $index;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->resultFactory = $this->createPartialMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create']
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->index = $objectManager->getObject(
            \Magento\Company\Controller\Account\Index::class,
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
        $resultPage = $this->createMock(\Magento\Framework\View\Result\Page::class);
        $title = $this->createMock(\Magento\Framework\View\Page\Title::class);
        $config = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $config->expects($this->once())->method('getTitle')->willReturn($title);
        $resultPage->expects($this->once())->method('getConfig')->willReturn($config);
        $this->resultFactory->expects($this->any())->method('create')->willReturn($resultPage);
        $this->assertEquals($resultPage, $this->index->execute());
    }
}
