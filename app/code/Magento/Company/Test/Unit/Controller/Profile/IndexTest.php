<?php

namespace Magento\Company\Test\Unit\Controller\Profile;

/**
 * Class IndexTest.
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\StatusServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleConfig;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureManager;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Company\Controller\Profile\Index|\PHPUnit_Framework_MockObject_MockObject
     */
    private $index;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->resultFactory =
            $this->createPartialMock(\Magento\Framework\Controller\ResultFactory::class, ['create']);
        $this->moduleConfig = $this->getMockBuilder(\Magento\Company\Api\StatusServiceInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isActive'])
            ->getMockForAbstractClass();
        $this->resultJsonFactory = $this
            ->createMock(\Magento\Framework\Controller\Result\JsonFactory::class);
        $this->structureManager = $this->createMock(\Magento\Company\Model\Company\Structure::class);
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->index = $objectManager->getObject(
            \Magento\Company\Controller\Profile\Index::class,
            [
                'resultFactory' => $this->resultFactory,
                'moduleConfig' => $this->moduleConfig,
                'resultJsonFactory' => $this->resultJsonFactory,
                'structureManager' => $this->structureManager,
                'logger' => $this->logger,

            ]
        );
    }

    /**
     * Test execute
     */
    public function testExecute()
    {
        $resultPage = $this->createPartialMock(
            \Magento\Framework\View\Result\Page::class,
            ['getConfig', 'getTitle', 'set']
        );
        $this->resultFactory->expects($this->any())->method('create')->willReturn($resultPage);
        $resultPage->expects($this->any())->method('getConfig')->willReturnSelf();
        $resultPage->expects($this->any())->method('getTitle')->willReturnSelf();
        $resultPage->expects($this->any())->method('set')->willReturnSelf();

        $this->assertInstanceOf(\Magento\Framework\View\Result\Page::class, $this->index->execute());
    }
}
