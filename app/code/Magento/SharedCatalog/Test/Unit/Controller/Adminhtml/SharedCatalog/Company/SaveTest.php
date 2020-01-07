<?php

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog\Company;

/**
 * Test for controller \Adminhtml\SharedCatalog\Company\Save.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\CompanyFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyStorageFactoryMock;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyStorageMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageFactoryMock;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepositoryMock;

    /**
     * @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var \Magento\SharedCatalog\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companySharedCatalogManagement;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectMock;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Company\Save
     */
    private $saveController;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->setMethods(['addSuccessMessage', 'addErrorMessage'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->resultRedirectFactoryMock = $this
            ->getMockBuilder(\Magento\Backend\Model\View\Result\RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->companyStorageFactoryMock = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\CompanyFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $this->companyStorageMock = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Company::class)
            ->setMethods(['getAssignedCompaniesIds', 'getUnassignedCompaniesIds'])
            ->disableOriginalConstructor()->getMock();

        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->setMethods(['critical'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'create'])
            ->disableOriginalConstructor()->getMock();

        $this->companySharedCatalogManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\CompanyManagementInterface::class)
            ->setMethods(['assignCompanies', 'unassignCompanies'])
            ->getMockForAbstractClass();

        $this->sharedCatalogRepositoryMock = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->sharedCatalogMock = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->setMethods(['getId', 'getCustomerGroupId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->resultPageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()->getMock();

        $this->redirectMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()->getMock();

        $this->companyRepository = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->setMethods(['getList'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->saveController = $objectManager->getObject(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Company\Save::class,
            [
                'logger' => $this->loggerMock,
                '_request' => $this->requestMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'messageManager' => $this->messageManagerMock,
                'sharedCatalogRepository' => $this->sharedCatalogRepositoryMock,
                'companySharedCatalogManagement' => $this->companySharedCatalogManagement,
                'companyStorageFactory' => $this->companyStorageFactoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'companyRepository' => $this->companyRepository,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock
            ]
        );
    }

    /**
     * Test for method Execute.
     *
     * @param bool $isContinue
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute($isContinue)
    {
        $calls = [
            'companyStorageMock_getAssignedCompaniesIds' => 1,
            'companyStorageMock_getUnassignedCompaniesIds' => 1
        ];

        $sharedCatalogId = 15;

        $configureKey = '236523dsf3';
        $sharedCatalogIdUrlParam = \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM;
        $mapForGetParamMethod = [
            [\Magento\SharedCatalog\Model\Form\Storage\UrlBuilder::class, null, $configureKey],
            [$sharedCatalogIdUrlParam, null, $sharedCatalogId],
            ['back', null, $isContinue]
        ];
        $this->requestMock->expects($this->exactly(3))->method('getParam')->willReturnMap($mapForGetParamMethod);

        $this->prepareCompanyStorageFactory($calls);

        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()->getMock();

        $this->searchCriteriaBuilder->expects($this->exactly(3))->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->exactly(2))->method('create')->willReturn($searchCriteria);

        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $companySearchResult = $this->getMockBuilder(\Magento\Company\Api\Data\CompanySearchResultsInterface::class)
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $assignedCompanies = [$company];
        $companySearchResult->expects($this->exactly(2))->method('getItems')->willReturn($assignedCompanies);

        $this->companyRepository->expects($this->exactly(2))->method('getList')->willReturn($companySearchResult);

        $this->sharedCatalogMock->expects($this->exactly(3))->method('getId')->willReturn($sharedCatalogId);
        $customerGroupId = 345;
        $this->sharedCatalogMock->expects($this->exactly(1))->method('getCustomerGroupId')
            ->willReturn($customerGroupId);

        $this->sharedCatalogRepositoryMock->expects($this->once())->method('get')->with($sharedCatalogId)
            ->willReturn($this->sharedCatalogMock);

        $this->companySharedCatalogManagement->expects($this->exactly(1))->method('assignCompanies')
            ->with($sharedCatalogId, $assignedCompanies)->willReturnSelf();
        $this->companySharedCatalogManagement->expects($this->exactly(1))->method('unassignCompanies')
            ->with($sharedCatalogId, $assignedCompanies)->willReturnSelf();

        $this->messageManagerMock->expects($this->once())->method('addSuccessMessage');

        $this->resultRedirectFactoryMock->expects($this->once())->method('create')->willReturn($this->redirectMock);

        $this->redirectMock->expects($this->once())->method('setPath')->willReturnSelf();

        $this->assertEquals($this->redirectMock, $this->saveController->execute());
    }

    /**
     * Data provider for execute() test.
     *
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * Prepare CompanyStorageFactory mock.
     *
     * @param array $calls
     * @return void
     */
    private function prepareCompanyStorageFactory(array $calls)
    {
        $companyIds = [1, 4, 6];
        $this->companyStorageMock->expects($this->exactly($calls['companyStorageMock_getAssignedCompaniesIds']))
            ->method('getAssignedCompaniesIds')->willReturn($companyIds);
        $this->companyStorageMock->expects($this->exactly($calls['companyStorageMock_getUnassignedCompaniesIds']))
            ->method('getUnassignedCompaniesIds')->willReturn($companyIds);

        $this->companyStorageFactoryMock->expects($this->once())->method('create')
            ->willReturn($this->companyStorageMock);
    }

    /**
     * Test for execute() with Exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $calls = [
            'companyStorageMock_getAssignedCompaniesIds' => 0,
            'companyStorageMock_getUnassignedCompaniesIds' => 0
        ];

        $configureKey = '236523dsf3';
        $sharedCatalogId = 19;
        $mapForGetParamMethod = [
            ['id', null, $sharedCatalogId],
            [\Magento\SharedCatalog\Model\Form\Storage\UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY, null, $configureKey]
        ];
        $this->requestMock->expects($this->exactly(2))->method('getParam')->willReturnMap($mapForGetParamMethod);

        $this->prepareCompanyStorageFactory($calls);

        $exception = new \Exception();

        $this->sharedCatalogRepositoryMock->expects($this->exactly(1))->method('get')->willThrowException($exception);

        $this->loggerMock->expects($this->exactly(1))->method('critical');

        $this->messageManagerMock->expects($this->exactly(1))->method('addErrorMessage')->willReturnSelf();

        $this->resultRedirectFactoryMock->expects($this->once())->method('create')->willReturn($this->redirectMock);

        $this->redirectMock->expects($this->once())->method('setPath')->willReturnSelf();

        $this->assertEquals($this->redirectMock, $this->saveController->execute());
    }
}
