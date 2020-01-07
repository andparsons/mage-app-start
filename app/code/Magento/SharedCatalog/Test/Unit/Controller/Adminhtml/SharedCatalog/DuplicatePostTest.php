<?php

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog;

use Magento\Framework\Exception\LocalizedException;

/**
 * Test for class DuplicatePost.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DuplicatePostTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectFactory;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirect;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogBuilder;

    /**
     * @var \Magento\SharedCatalog\Model\Duplicator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $duplicateManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalog;

    /**
     * @var \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\DuplicatePost
     */
    private $duplicateController;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->redirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()->getMock();

        $this->redirectFactory = $this->getMockBuilder(\Magento\Backend\Model\View\Result\RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->redirectFactory->expects($this->once())->method('create')->willReturn($this->redirect);

        $this->resultPageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()->getMock();

        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()->getMock();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->setMethods(['addSuccess'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->sharedCatalogRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->setMethods(['save'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->sharedCatalogBuilder = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalogBuilder::class)
            ->disableOriginalConstructor()->getMock();

        $this->duplicateManager = $this->getMockBuilder(\Magento\SharedCatalog\Model\Duplicator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->duplicateController = $this->objectManager->getObject(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\DuplicatePost::class,
            [
                'resultPageFactory' => $this->resultPageFactory,
                'sharedCatalogBuilder' => $this->sharedCatalogBuilder,
                'duplicateManager' => $this->duplicateManager,
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
                '_request' => $this->request,
                'resultRedirectFactory' => $this->redirectFactory,
                'resultFactory' => $this->resultFactory,
                'messageManager' => $this->messageManager
            ]
        );
    }

    /**
     * Test execute method with throw LocalizedException with edit redirect.
     *
     * @return void
     */
    public function testExecuteWithLocalizedExceptionEdit()
    {
        $this->sharedCatalog->expects($this->exactly(2))->method('getId')->willReturn(1);
        $this->sharedCatalogBuilder->expects($this->once())->method('build')->willReturn($this->sharedCatalog);
        $this->sharedCatalogRepository->expects($this->once())
            ->method('save')->willThrowException(new LocalizedException(new \Magento\Framework\Phrase('error')));
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->redirect->expects($this->once())->method('setPath')
            ->with('shared_catalog/sharedCatalog/edit')->willReturnSelf();
        $this->duplicateController->execute();
    }

    /**
     * Test execute method with throw LocalizedException with create redirect.
     *
     * @return void
     */
    public function testExecuteWithLocalizedExceptionCreate()
    {
        $this->sharedCatalog->expects($this->once())->method('getId')->willReturn(0);
        $this->sharedCatalogBuilder->expects($this->once())->method('build')->willReturn($this->sharedCatalog);
        $this->sharedCatalogRepository->expects($this->once())
            ->method('save')->willThrowException(new LocalizedException(new \Magento\Framework\Phrase('error')));
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->redirect->expects($this->once())->method('setPath')
            ->with('shared_catalog/sharedCatalog/duplicate')->willReturnSelf();
        $this->duplicateController->execute();
    }

    /**
     * Test execute method with throw Exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $this->sharedCatalog->expects($this->never())->method('getId')->willReturn(0);

        $this->sharedCatalogBuilder->expects($this->once())->method('build')->willReturn($this->sharedCatalog);
        $this->sharedCatalogRepository->expects($this->once())
            ->method('save')->willThrowException(new \Exception());
        $this->messageManager->expects($this->once())->method('addExceptionMessage');
        $this->redirect->expects($this->once())->method('setPath')
            ->with('shared_catalog/sharedCatalog/duplicate')->willReturnSelf();
        $this->duplicateController->execute();
    }

    /**
     * Test execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $sharedCatalog->expects($this->exactly(2))->method('getId')->willReturn(1);
        $this->sharedCatalogBuilder->expects($this->once())->method('build')->willReturn($sharedCatalog);
        $this->sharedCatalogRepository->expects($this->once())->method('save');
        $this->duplicateManager->expects($this->atLeastOnce())->method('duplicateCatalog');
        $this->messageManager->expects($this->once())->method('addSuccess');
        $this->redirect->expects($this->once())->method('setPath')
            ->with('shared_catalog/sharedCatalog/index')->willReturnSelf();
        $this->duplicateController->execute();
    }
}
