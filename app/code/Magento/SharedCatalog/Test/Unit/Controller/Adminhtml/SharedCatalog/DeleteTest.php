<?php

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog;

/**
 * Test controller for Adminhtml\SharedCatalog\Delete.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Sample Id
     * @var string
     */
    const ID = '123';

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageFactory;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogManagement;

    /**
     * @var \Magento\SharedCatalog\Model\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Delete
     */
    private $deleteMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->resultPageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()->getMock();

        $this->sharedCatalogManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogManagementInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->sharedCatalogRepository = $this->getMockBuilder(\Magento\SharedCatalog\Model\Repository::class)
            ->setMethods(['get', 'delete'])
            ->disableOriginalConstructor()->getMock();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->resultRedirectFactory = $this
            ->getMockBuilder(\Magento\Backend\Model\View\Result\RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * Test for method Execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $sampleRedirectResult = 'sample result'; //sample result
        $redirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()->getMock();

        $redirect->expects($this->once())->method('setPath')->with('shared_catalog/sharedCatalog/index')
            ->willReturn($sampleRedirectResult);

        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($redirect);

        $urlParam = \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM;
        $this->request->expects($this->exactly(1))->method('getParam')->with($urlParam)->willReturn(static::ID);

        $this->messageManager->expects($this->once())->method('addSuccessMessage');

        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $sharedCatalog->method('getId')->willReturn(static::ID);
        $sharedCatalog->method('getCustomerGroupId')->willReturn(static::ID);

        $this->sharedCatalogRepository->expects($this->atLeastOnce())->method('get')->with(static::ID)
            ->willReturn($sharedCatalog);
        $this->sharedCatalogRepository->expects($this->once())->method('delete');

        $this->createDeleteMock();

        $result = $this->deleteMock->execute();
        $this->assertInstanceOf(get_class($redirect), $result);
    }

    /**
     * Test for method Execute with Exception.
     *
     * @return void
     */
    public function testExecuteException()
    {
        $exceptionMessage = 'sample exception message'; //sample exception message
        $exception = new \Exception($exceptionMessage);

        $redirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()->getMock();

        $this->resultRedirectFactory->expects($this->exactly(2))->method('create')->willReturn($redirect);

        $urlParam = \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM;
        $this->request->expects($this->exactly(2))->method('getParam')->with($urlParam)->willReturn(static::ID);

        $this->messageManager->expects($this->once())->method('addErrorMessage')->with($exceptionMessage);

        $this->sharedCatalogRepository->expects($this->once())->method('get')->willThrowException($exception);

        $this->createDeleteMock();

        $result = $this->deleteMock->execute();
        $this->assertInstanceOf(get_class($redirect), $result);
    }

    /**
     * Create Delete mock.
     *
     * @return void
     */
    private function createDeleteMock()
    {
        $loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->deleteMock = $this->objectManager->getObject(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Delete::class,
            [
                'resultPageFactory' => $this->resultPageFactory,
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'logger' => $loggerMock,
                '_request' => $this->request,
                'messageManager' => $this->messageManager,
            ]
        );
    }
}
