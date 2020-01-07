<?php

namespace Magento\Company\Test\Unit\Controller\Adminhtml\Index;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class Mass.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mass extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $actionName = '';

    /**
     * @var string
     */
    protected $successMessage = '';

    /**
     * @var \Magento\Customer\Controller\Adminhtml\Index\MassDelete
     */
    protected $massAction;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Company\Model\ResourceModel\Company\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $companyCollectionMock;

    /**
     * @var \Magento\Company\Model\ResourceModel\Company\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $companyCollectionFactoryMock;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $companyRepositoryMock;

    /**
     * SetUp
     *
     * @return void
     */
    protected function setUp()
    {
        if (empty($this->actionName)) {
            return;
        }
        $objectManagerHelper = new ObjectManagerHelper($this);
        $resultRedirectFactory = $this->createMock(\Magento\Backend\Model\View\Result\RedirectFactory::class);
        $this->objectManagerMock = $this->createPartialMock(
            \Magento\Framework\ObjectManager\ObjectManager::class,
            ['create']
        );
        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\Manager::class);
        $this->companyCollectionMock =
            $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Company\Collection::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->companyCollectionFactoryMock =
            $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Company\CollectionFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $redirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMock();
        $redirectMock->expects($this->any())->method('setPath')->willReturnSelf();
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
        $this->filterMock = $this->createMock(\Magento\Ui\Component\MassAction\Filter::class);
        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->companyCollectionMock)
            ->willReturnArgument(0);
        $this->companyCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->companyCollectionMock);
        $this->companyRepositoryMock = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->massAction = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Adminhtml\Index\Mass::class . $this->actionName,
            [
                'filter' => $this->filterMock,
                'collectionFactory' => $this->companyCollectionFactoryMock,
                'companyRepository' => $this->companyRepositoryMock,
                'resultRedirectFactory' => $resultRedirectFactory,
                'resultFactory' => $resultFactoryMock,
                'messageManager' => $this->messageManagerMock
            ]
        );
    }
}
