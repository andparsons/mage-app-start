<?php

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog;

use Magento\Framework\App\Action\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\CollectionFactory as SharedCatalogCollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class MassTest extends \PHPUnit\Framework\TestCase
{
    protected $actionName = '';

    protected $successMessage = '';

    /**
     * @var \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Mass
     */
    protected $massAction;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sharedCatalogCollectionMock;

    /**
     * @var SharedCatalogCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sharedCatalogCollectionFactoryMock;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterMock;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sharedCatalogRepositoryMock;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sharedCatalogManagement;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalog|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sharedCatalog;

    public function setUp()
    {
        if (empty($this->actionName)) {
            return;
        }
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->contextMock = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $resultRedirectFactory = $this->createMock(\Magento\Backend\Model\View\Result\RedirectFactory::class);
        $this->responseMock = $this->createMock(\Magento\Framework\App\ResponseInterface::class);
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->objectManagerMock =
            $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, ['create']);
        $this->sharedCatalogManagement =
            $this->createMock(\Magento\SharedCatalog\Api\SharedCatalogManagementInterface::class);
        $this->sharedCatalog = $this->getMockForAbstractClass(
            \Magento\SharedCatalog\Model\SharedCatalog::class,
            ['getId'],
            '',
            false,
            false,
            true,
            []
        );
        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\Manager::class);
        $this->sharedCatalogCollectionMock =
            $this->getMockBuilder(\Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->sharedCatalogCollectionFactoryMock =
            $this->getMockBuilder(\Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\CollectionFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $redirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($redirectMock);

        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirectMock);

        $this->contextMock->expects($this->once())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects($this->once())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($resultRedirectFactory);
        $this->contextMock->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($resultFactoryMock);
        $this->filterMock = $this->createMock(\Magento\Ui\Component\MassAction\Filter::class);
        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->sharedCatalogCollectionMock)
            ->willReturnArgument(0);
        $this->sharedCatalogCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->sharedCatalogCollectionMock);
        $this->sharedCatalogRepositoryMock = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->massAction = $objectManagerHelper->getObject(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Mass::class . $this->actionName,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->sharedCatalogCollectionFactoryMock,
                'sharedCatalogRepository' => $this->sharedCatalogRepositoryMock,
            ]
        );
    }
}
