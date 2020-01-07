<?php

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog;

use Magento\SharedCatalog\Model\SharedCatalogBuilder;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Test Admin SharedCatalog Save controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirect;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogFactory;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var SharedCatalogBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogBuilder;

    /**
     * @var \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Save
     */
    private $controller;

    /**
     * @var SharedCatalogInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalog;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->redirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory = $this
            ->getMockBuilder(\Magento\Backend\Model\View\Result\RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->setMethods(['addSuccess', 'addException', 'addErrorMessage'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->sharedCatalogRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->setMethods(['save', 'get'])
            ->getMockForAbstractClass();
        $this->sharedCatalogManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogManagementInterface::class)
            ->setMethods(['createSharedCatalog'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request = $this
            ->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMockForAbstractClass();
        $this->sharedCatalogFactory = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalogFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->setMethods(['setData', 'setId', 'getName', 'getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->sharedCatalogBuilder = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalogBuilder::class)
            ->setMethods(['build'])
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Save::class,
            [
                '_request' => $this->request,
                'messageManager' => $this->messageManager,
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
                'sharedCatalogBuilder' => $this->sharedCatalogBuilder,
                'logger' => $this->loggerMock,
                'resultRedirectFactory' => $this->resultRedirectFactory
            ]
        );
    }

    /**
     * Test execute method.
     *
     * @param bool $isContinue
     * @param string $setPathFirstArg
     * @param array $setPathSecondArg
     * @dataProvider executeDataProvider
     * @return void
     */
    public function testExecute($isContinue, $setPathFirstArg, array $setPathSecondArg)
    {
        $sharedCatalogId = 2;
        $successMessage = __('You saved the shared catalog.');

        $this->sharedCatalogBuilder->expects($this->once())->method('build')->willReturn($this->sharedCatalog);
        $this->sharedCatalogRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->sharedCatalog)
            ->willReturn($sharedCatalogId);
        $this->messageManager->expects($this->once())->method('addSuccess')->with($successMessage)->willReturnSelf();
        $this->sharedCatalog->expects($this->once())->method('getId')->willReturn($sharedCatalogId);
        $mapForGerParamMethod = [
            ['back', null, $isContinue],
            [SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM, null, $sharedCatalogId],
        ];
        $this->request->expects($this->exactly(2))->method('getParam')->willReturnMap($mapForGerParamMethod);
        $this->redirect
            ->expects($this->once())
            ->method('setPath')
            ->with($setPathFirstArg, $setPathSecondArg)
            ->willReturnSelf();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($this->redirect);

        $this->assertEquals($this->redirect, $this->controller->execute());
    }

    /**
     * Data provider for execute method.
     *
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [
                true,
                'shared_catalog/sharedCatalog/edit',
                ['shared_catalog_id' => 2]
            ],
            [
                false,
                'shared_catalog/sharedCatalog/index',
                []
            ]
        ];
    }

    /**
     * Test execute method throes exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $exception = new \Exception();
        $exceptionMessage = __('Something went wrong while saving the shared catalog.');

        $sharedCatalogUrlParam = SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM;
        $sharedCatalogId = 23;
        $this->request->expects($this->exactly(1))->method('getParam')->with($sharedCatalogUrlParam)
            ->willReturn($sharedCatalogId);

        $this->sharedCatalogBuilder->expects($this->once())->method('build')->willThrowException($exception);
        $this->messageManager
            ->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, $exceptionMessage)
            ->willReturnSelf();
        $this->redirect
            ->expects($this->once())
            ->method('setPath')
            ->with('shared_catalog/sharedCatalog/index')
            ->willReturnSelf();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($this->redirect);

        $this->assertEquals($this->redirect, $this->controller->execute());
    }

    /**
     * Test execute method throws LocalizedException.
     *
     * @param int|null $sharedCatalogId
     * @param string $setPathFirstArg
     * @param array $setPathSecondArg
     * @param int $getIdCounter
     * @dataProvider executeWithLocalizedExceptionDataProvider
     * @return void
     */
    public function testExecuteWithLocalizedException(
        $sharedCatalogId,
        $setPathFirstArg,
        array $setPathSecondArg,
        $getIdCounter
    ) {
        $exceptionMessage = 'Localized Exception';
        $exception = new \Magento\Framework\Exception\LocalizedException(__($exceptionMessage));

        $this->sharedCatalogBuilder->expects($this->once())->method('build')->willReturn($this->sharedCatalog);
        $this->sharedCatalogRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->sharedCatalog)
            ->willThrowException($exception);
        $this->messageManager
            ->expects($this->once())
            ->method('addErrorMessage')
            ->with($exceptionMessage)
            ->willReturnSelf();
        $this->sharedCatalog->expects($this->exactly($getIdCounter))->method('getId')->willReturn($sharedCatalogId);
        $this->redirect
            ->expects($this->once())
            ->method('setPath')
            ->with($setPathFirstArg, $setPathSecondArg)
            ->willReturnSelf();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($this->redirect);

        $this->assertEquals($this->redirect, $this->controller->execute());
    }

    /**
     * Data provider for execute() with LocalizedException.
     *
     * @return array
     */
    public function executeWithLocalizedExceptionDataProvider()
    {
        return [
            [
                2,
                'shared_catalog/sharedCatalog/edit',
                ['shared_catalog_id' => 2],
                2
            ],
            [
                null,
                'shared_catalog/sharedCatalog/create',
                [],
                1
            ]
        ];
    }
}
