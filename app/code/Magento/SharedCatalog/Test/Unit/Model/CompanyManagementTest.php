<?php

namespace Magento\SharedCatalog\Test\Unit\Model;

/**
 * CompanyManagement unit test.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompanyManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\Management|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogManagement;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\SharedCatalog\Model\CompanyManagement
     */
    private $companyManagement;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->sharedCatalogManagement = $this->getMockBuilder(\Magento\SharedCatalog\Model\Management::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalogRepository = $this->getMockBuilder(
            \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyRepository = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resource = $this->getMockBuilder(\Magento\SharedCatalog\Model\ResourceModel\SharedCatalog::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyManagement = $objectManager->getObject(
            \Magento\SharedCatalog\Model\CompanyManagement::class,
            [
                'sharedCatalogManagement' => $this->sharedCatalogManagement,
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'companyRepository' => $this->companyRepository,
                'resource' => $this->resource
            ]
        );
    }

    /**
     * Test getCompanies.
     *
     * @return void
     */
    public function testGetCompanies()
    {
        $id = 1;
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->sharedCatalogRepository->expects($this->once())->method('get')->with($id)
            ->willReturn($sharedCatalog);
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder->expects($this->once())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company->expects($this->once())->method('getId')->willReturn($id);
        $companySearchResult = $this->getMockBuilder(\Magento\Company\Api\Data\CompanySearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companySearchResult->expects($this->once())->method('getItems')->willReturn([$company]);
        $this->companyRepository->expects($this->once())->method('getList')->willReturn($companySearchResult);

        $this->assertEquals(json_encode([$id]), $this->companyManagement->getCompanies($id));
    }

    /**
     * Test assignCompanies method.
     *
     * @return void
     */
    public function testAssignCompanies()
    {
        $id = 1;
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sharedCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($id);
        $this->sharedCatalogRepository->expects($this->once())->method('get')->with($id)
            ->willReturn($sharedCatalog);
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company->expects($this->once())->method('getId')->willReturn($id);
        $company->expects($this->once())->method('setCustomerGroupId')->willReturnSelf();
        $this->companyRepository->expects($this->once())->method('save')->with($company)->willReturn($company);
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $companySearchResult = $this->getMockBuilder(\Magento\Company\Api\Data\CompanySearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companySearchResult->expects($this->once())->method('getItems')->willReturn([$company]);
        $this->companyRepository->expects($this->once())->method('getList')->willReturn($companySearchResult);
        $this->resource->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $this->resource->expects($this->once())->method('commit')->willReturnSelf();

        $this->assertTrue($this->companyManagement->assignCompanies($id, [$company]));
    }

    /**
     * Test assignCompanies with Exception.
     *
     * @return void
     * @expectedException \Exception
     */
    public function testAssignCompaniesWithException()
    {
        $id = 1;
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sharedCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($id);
        $this->sharedCatalogRepository->expects($this->once())->method('get')->with($id)
            ->willReturn($sharedCatalog);
        $exception = new \Exception(__('Exception'));
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company->expects($this->once())->method('getId')->willReturn($id);
        $company->expects($this->once())->method('setCustomerGroupId')->willReturnSelf();
        $this->companyRepository->expects($this->once())->method('save')->with($company)
            ->willThrowException($exception);
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $companySearchResult = $this->getMockBuilder(\Magento\Company\Api\Data\CompanySearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companySearchResult->expects($this->once())->method('getItems')->willReturn([$company]);
        $this->companyRepository->expects($this->once())->method('getList')->willReturn($companySearchResult);
        $this->resource->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $this->resource->expects($this->once())->method('rollBack')->willReturnSelf();

        $this->assertTrue($this->companyManagement->assignCompanies($id, [$company]));
    }

    /**
     * Test unassignCompanies.
     *
     * @return void
     */
    public function testUnassignCompanies()
    {
        $id = 1;
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sharedCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($id);
        $this->sharedCatalogRepository->expects($this->once())->method('get')->with($id)
            ->willReturn($sharedCatalog);
        $publicCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $publicCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn(2);
        $this->sharedCatalogManagement->expects($this->once())->method('getPublicCatalog')->willReturn($publicCatalog);
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company->expects($this->once())->method('getId')->willReturn($id);
        $company->expects($this->once())->method('setCustomerGroupId')->willReturnSelf();
        $this->companyRepository->expects($this->once())->method('save')->with($company)->willReturn($company);
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $companySearchResult = $this->getMockBuilder(\Magento\Company\Api\Data\CompanySearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companySearchResult->expects($this->once())->method('getItems')->willReturn([$company]);
        $this->companyRepository->expects($this->once())->method('getList')->willReturn($companySearchResult);
        $this->resource->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $this->resource->expects($this->once())->method('commit')->willReturnSelf();

        $this->assertTrue($this->companyManagement->unassignCompanies($id, [$company]));
    }

    /**
     * Test unassignCompanies with Exception.
     *
     * @return void
     * @expectedException \Exception
     */
    public function testUnassignCompaniesWithException()
    {
        $id = 1;
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sharedCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($id);
        $this->sharedCatalogRepository->expects($this->once())->method('get')->with($id)
            ->willReturn($sharedCatalog);
        $publicCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $publicCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn(2);
        $this->sharedCatalogManagement->expects($this->once())->method('getPublicCatalog')->willReturn($publicCatalog);
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $exception = new \Exception(__('Exception'));
        $company->expects($this->once())->method('getId')->willReturn($id);
        $company->expects($this->once())->method('setCustomerGroupId')->willReturnSelf();
        $this->companyRepository->expects($this->once())->method('save')->with($company)
            ->willThrowException($exception);
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $companySearchResult = $this->getMockBuilder(\Magento\Company\Api\Data\CompanySearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companySearchResult->expects($this->once())->method('getItems')->willReturn([$company]);
        $this->companyRepository->expects($this->once())->method('getList')->willReturn($companySearchResult);
        $this->resource->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $this->resource->expects($this->never())->method('commit')->willReturnSelf();
        $this->resource->expects($this->once())->method('rollBack')->willReturnSelf();

        $this->assertTrue($this->companyManagement->unassignCompanies($id, [$company]));
    }

    /**
     * Test unassignCompanies with Exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testUnassignCompaniesWithLocalizedException()
    {
        $id = 1;
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sharedCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($id);
        $this->sharedCatalogRepository->expects($this->once())->method('get')->with($id)
            ->willReturn($sharedCatalog);
        $publicCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $publicCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn(1);
        $this->sharedCatalogManagement->expects($this->once())->method('getPublicCatalog')->willReturn($publicCatalog);
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->companyManagement->unassignCompanies($id, [$company]);
    }

    /**
     * Test unassignCompanies with empty companies ids.
     *
     * @return void
     */
    public function testUnassignCompaniesWithoutCompanies()
    {
        $id = 1;
        $this->sharedCatalogRepository->expects($this->never())->method('get');
        $this->sharedCatalogManagement->expects($this->never())->method('getPublicCatalog');

        $this->companyManagement->unassignCompanies($id, []);
    }

    /**
     * Test unassignAllCompanies.
     *
     * @return void
     */
    public function testUnassignAllCompanies()
    {
        $id = 1;
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sharedCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($id);
        $this->sharedCatalogRepository->expects($this->once())->method('get')->with($id)
            ->willReturn($sharedCatalog);
        $publicCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $publicCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn(2);
        $this->sharedCatalogManagement->expects($this->once())->method('getPublicCatalog')->willReturn($publicCatalog);
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company->expects($this->once())->method('setCustomerGroupId')->willReturnSelf();
        $this->companyRepository->expects($this->once())->method('save')->with($company)->willReturn($company);
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $companySearchResult = $this->getMockBuilder(\Magento\Company\Api\Data\CompanySearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companySearchResult->expects($this->once())->method('getItems')->willReturn([$company]);
        $this->companyRepository->expects($this->once())->method('getList')->willReturn($companySearchResult);
        $this->resource->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $this->resource->expects($this->once())->method('commit')->willReturnSelf();

        $this->companyManagement->unassignAllCompanies($id);
    }

    /**
     * Test unassignAllCompanies with Exception.
     *
     * @return void
     * @expectedException \Exception
     */
    public function testUnassignAllCompaniesWithException()
    {
        $id = 1;
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sharedCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($id);
        $this->sharedCatalogRepository->expects($this->once())->method('get')->with($id)
            ->willReturn($sharedCatalog);
        $publicCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $publicCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn(2);
        $this->sharedCatalogManagement->expects($this->once())->method('getPublicCatalog')->willReturn($publicCatalog);
        $exception = new \Exception(__('Exception'));
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company->expects($this->once())->method('setCustomerGroupId')->willReturnSelf();
        $this->companyRepository->expects($this->once())->method('save')->with($company)
            ->willThrowException($exception);
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $companySearchResult = $this->getMockBuilder(\Magento\Company\Api\Data\CompanySearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companySearchResult->expects($this->once())->method('getItems')->willReturn([$company]);
        $this->companyRepository->expects($this->once())->method('getList')->willReturn($companySearchResult);
        $this->resource->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $this->resource->expects($this->never())->method('commit')->willReturnSelf();
        $this->resource->expects($this->once())->method('rollBack')->willReturnSelf();

        $this->companyManagement->unassignAllCompanies($id);
    }
}
