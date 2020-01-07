<?php

namespace Magento\Company\Test\Unit\Controller\Adminhtml\Customer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class MassStatusTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassStatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Controller\Adminhtml\Customer\MassStatus
     */
    protected $massAction;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit\Framework\MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit\Framework\MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit\Framework\MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection|\PHPUnit\Framework\MockObject_MockObject
     */
    protected $customerCollectionMock;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory|\PHPUnit\Framework\MockObject_MockObject
     */
    protected $customerCollectionFactoryMock;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter|\PHPUnit\Framework\MockObject_MockObject
     */
    protected $filterMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit\Framework\MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $resultRedirectFactory = $this->createMock(\Magento\Backend\Model\View\Result\RedirectFactory::class);
        $this->responseMock = $this->createMock(\Magento\Framework\App\ResponseInterface::class);
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->objectManagerMock = $this->createPartialMock(
            \Magento\Framework\ObjectManager\ObjectManager::class,
            ['create']
        );
        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\Manager::class);
        $this->customerCollectionMock =
            $this->getMockBuilder(\Magento\Customer\Model\ResourceModel\Customer\Collection::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->customerCollectionFactoryMock = $this->createPartialMock(
            \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory::class,
            ['create']
        );
        $redirect = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $redirect->expects($this->any())->method('setPath')->will($this->returnSelf());
        $resultRedirectFactory->expects($this->any())->method('create')->will($this->returnValue($redirect));
        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterMock = $this->createMock(\Magento\Ui\Component\MassAction\Filter::class);
        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->customerCollectionMock)
            ->willReturnArgument(0);
        $this->customerCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->customerCollectionMock);
        $customer = $this->createMock(\Magento\Customer\Model\Data\Customer::class);
        $companyAttributes =
            $this->createMock(\Magento\Company\Api\Data\CompanyCustomerInterface::class);
        $customerExtension = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setCompanyAttributes', 'getCompanyAttributes']
        );

        $customer->expects($this->any())->method('getExtensionAttributes')->willReturn($customerExtension);
        $customerExtension->expects($this->any())->method('getCompanyAttributes')->willReturn($companyAttributes);
        $this->customerRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->setMethods(['setStatus'])
            ->getMockForAbstractClass();
        $this->customerRepositoryMock->expects($this->any())->method('getById')->willReturn($customer);
        $this->massAction = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Adminhtml\Customer\MassStatus::class,
            [
                'filter' => $this->filterMock,
                'collectionFactory' => $this->customerCollectionFactoryMock,
                'customerRepository' => $this->customerRepositoryMock,
                'resultRedirectFactory' => $resultRedirectFactory,
                'messageManager' => $this->messageManagerMock
            ]
        );
    }

    /**
     * Test execute
     *
     * @return void
     */
    public function testExecute()
    {
        $customersIds = [10, 11, 12];
        $this->customerCollectionMock->expects($this->any())
            ->method('getAllIds')
            ->willReturn($customersIds);

        $this->customerRepositoryMock->expects($this->any())
            ->method('setStatus')
            ->willReturnMap([[10, true], [11, true], [12, true]]);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('A total of %1 record(s) were updated.', count($customersIds)));

        $this->resultRedirectMock->expects($this->any())
            ->method('setPath')
            ->with('customer/*/index')
            ->willReturnSelf();

        $this->massAction->execute();
    }
}
