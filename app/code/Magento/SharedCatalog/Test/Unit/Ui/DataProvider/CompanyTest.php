<?php

namespace Magento\SharedCatalog\Test\Unit\Ui\DataProvider;

use Magento\SharedCatalog\Ui\DataProvider\Collection\Grid\CompanyFactory;

/**
 * Unit test for Company data provider.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CompanyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private $filterValue = 'test value';

    /**
     * @var string
     */
    private $key = 'test key';

    /**
     * @var array
     */
    private $assignedCompaniesIds = ['test id'];

    /**
     * @var string
     */
    private $sharedCatalogId = 'test sharedCatalog id';

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var CompanyFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\CompanyFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyStorageFactory;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogRepository;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Collection\Grid\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    /**
     * @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalog;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storage;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogManagement;

    /**
     * @var \Magento\Framework\Api\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filter;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteria;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Company\Api\Data\CompanySearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companySearchResults;

    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Company
     */
    private $companyDataProvider;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->collectionFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Ui\DataProvider\Collection\Grid\CompanyFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->storage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Company::class)
            ->disableOriginalConstructor()->getMock();
        $this->companyStorageFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\CompanyFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->catalogRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->setMethods(['get', 'getBySharedCatalogId', 'getPublicCatalog'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->collection = $this->getMockBuilder(\Magento\SharedCatalog\Ui\DataProvider\Collection\Grid\Company::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->catalogManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogManagementInterface::class)
            ->setMethods(['getPublicCatalog'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->filter = $this->getMockBuilder(\Magento\Framework\Api\Filter::class)
            ->setMethods(['getField', 'getValue', 'getConditionType'])
            ->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'create'])
            ->disableOriginalConstructor()->getMock();
        $this->searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->companyRepository = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->companySearchResults = $this
            ->getMockBuilder(\Magento\Company\Api\Data\CompanySearchResultsInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->request->expects($this->once())->method('getParam')
            ->with(\Magento\SharedCatalog\Model\Form\Storage\UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY)
            ->willReturn($this->key);
        $this->companyStorageFactory->expects($this->once())
            ->method('create')->with(['key' => $this->key])->willReturn($this->storage);

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyDataProvider = $this->objectManager->getObject(
            \Magento\SharedCatalog\Ui\DataProvider\Company::class,
            [
                'request' => $this->request,
                'collectionFactory' => $this->collectionFactory,
                'companyStorageFactory' =>$this->companyStorageFactory,
                'catalogManagement' => $this->catalogManagement,
                'catalogRepository' => $this->catalogRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'companyRepository' => $this->companyRepository,
                'data' => [
                    'config' => [
                        'filter_url_params' => [
                            'shared_catalog_id' => 1
                        ],
                        'update_url' => '',
                    ]
                ]
            ]
        );
    }

    /**
     * Test for addFilter method.
     *
     * @return void
     */
    public function testAddFilter()
    {
        $field = 'test field';
        $conditionType = 'test condition type';
        $this->filter->expects($this->once())->method('getValue')->willReturn($this->filterValue);
        $this->filter->expects($this->exactly(2))->method('getField')->willReturn($field);
        $this->filter->expects($this->once())->method('getConditionType')->willReturn($conditionType);
        $this->collection->expects($this->once())
            ->method('addFieldToFilter')->with($field, [$conditionType => $this->filterValue])->willReturnSelf();
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($this->collection);
        $this->companyDataProvider->addFilter($this->filter);
    }

    /**
     * Test for addFilter method with is_current field.
     *
     * @return void
     */
    public function testAddFilterWithIsCurrent()
    {
        $this->filter->expects($this->once())->method('getValue')->willReturn($this->filterValue);
        $this->filter->expects($this->once())->method('getField')->willReturn('is_current');
        $this->collection->expects($this->once())->method('addIdFilter')
            ->with($this->assignedCompaniesIds, !$this->filterValue);
        $this->storage->expects($this->once())
            ->method('getAssignedCompaniesIds')->willReturn($this->assignedCompaniesIds);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($this->collection);
        $this->companyDataProvider->addFilter($this->filter);
    }

    /**
     * Test for addFilter method with shared_catalog_id field.
     *
     * @return void
     */
    public function testAddFilterWithSharedCatalogId()
    {
        $this->filter->expects($this->once())->method('getValue')->willReturn($this->filterValue);
        $this->filter->expects($this->once())->method('getField')->willReturn('shared_catalog_id');
        $this->storage->expects($this->atLeastOnce())->method('getSharedCatalogId')->willReturn($this->sharedCatalogId);
        $this->catalogRepository->expects($this->at(0))
            ->method('get')->with($this->sharedCatalogId)->willReturn($this->sharedCatalog);
        $this->catalogRepository->expects($this->at(1))
            ->method('get')->with($this->filterValue)->willReturn($this->sharedCatalog);
        $this->storage->expects($this->once())
            ->method('getAssignedCompaniesIds')->willReturn($this->assignedCompaniesIds);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')->willReturn($this->searchCriteriaBuilder);
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($this->searchCriteria);
        $this->companyRepository->expects($this->once())
            ->method('getList')->with($this->searchCriteria)->willReturn($this->companySearchResults);
        $this->companySearchResults->expects($this->once())
            ->method('getItems')->willReturn($this->assignedCompaniesIds);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($this->collection);
        $publicCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->catalogManagement->expects($this->once())->method('getPublicCatalog')->willReturn($publicCatalog);
        $publicCatalog->expects($this->once())->method('getId')->willReturn($this->filterValue);
        $this->storage->expects($this->once())->method('getUnassignedCompaniesIds')->willReturn([]);
        $this->companyDataProvider->addFilter($this->filter);
    }

    /**
     * Test for addFilter method with shared_catalog_id field that matches current catalog id.
     *
     * @return void
     */
    public function testAddFilterWithSameSharedCatalogId()
    {
        $this->filter->expects($this->once())->method('getValue')->willReturn($this->filterValue);
        $this->filter->expects($this->once())->method('getField')->willReturn('shared_catalog_id');
        $this->storage->expects($this->atLeastOnce())->method('getSharedCatalogId')->willReturn($this->sharedCatalogId);
        $this->catalogRepository->expects($this->at(0))
            ->method('get')->with($this->sharedCatalogId)->willReturn($this->sharedCatalog);
        $this->collection->expects($this->once())->method('addIdFilter')
            ->with($this->assignedCompaniesIds, !$this->filterValue);
        $this->storage->expects($this->once())
            ->method('getAssignedCompaniesIds')->willReturn($this->assignedCompaniesIds);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($this->collection);
        $this->sharedCatalog->expects($this->once())->method('getId')->willReturn($this->filterValue);
        $this->companyDataProvider->addFilter($this->filter);
    }

    /**
     * Test for getData method.
     *
     * @param bool $isAssigned
     * @return void
     * @dataProvider getDataDataProvider
     */
    public function testGetData($isAssigned)
    {
        $entityId = 2;
        $dataObject = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(
                [
                    'getEntityId',
                    'getSharedCatalogId',
                    'setSharedCatalogId',
                    'setIsCurrent',
                    'setIsPublicCatalog',
                    'toArray'
                ]
            )
            ->disableOriginalConstructor()->getMock();
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($this->collection);
        $this->collection->expects($this->once())->method('addIsCurrentColumn')->with($this->sharedCatalogId)
            ->willReturnSelf();
        $this->collection->expects($this->once())->method('getSize')->willReturn(1);
        $this->collection->expects($this->once())->method('getItems')->willReturn([$dataObject]);
        $dataObject->expects($this->atLeastOnce())->method('getEntityId')->willReturn($entityId);
        $this->storage->expects($this->once())
            ->method('isCompanyAssigned')->with($entityId)->willReturn($isAssigned);
        $this->storage->expects($this->once())
            ->method('isCompanyUnassigned')->with($entityId)->willReturn(!$isAssigned);
        $dataObject->expects($this->atLeastOnce())->method('getSharedCatalogId')->willReturn($this->sharedCatalogId);
        $this->storage->expects($this->atLeastOnce())
            ->method('getSharedCatalogId')->willReturn($this->sharedCatalogId);
        $dataObject->expects($this->once())
            ->method('setSharedCatalogId')->with($this->sharedCatalogId)->willReturnSelf();
        $dataObject->expects($this->once())->method('setIsCurrent')->with(1)->willReturnSelf();
        $dataObject->expects($this->once())->method('setIsPublicCatalog')->with(true)->willReturnSelf();
        $publicCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->catalogManagement->expects($this->atLeastOnce())
            ->method('getPublicCatalog')->willReturn($publicCatalog);
        $publicCatalog->expects($this->atLeastOnce())->method('getId')->willReturn($this->sharedCatalogId);
        $dataObject->expects($this->once())->method('toArray')->willReturn(['company_data']);
        $this->assertEquals(
            [
                'totalRecords' => 1,
                'items' => [['company_data']],
            ],
            $this->companyDataProvider->getData()
        );
    }

    /**
     * Test for getConfigData method.
     *
     * @return void
     */
    public function testGetConfigData()
    {
        $this->assertEquals(
            [
                'filter_url_params' => [
                    'shared_catalog_id' => 1
                ],
                'update_url' => 'shared_catalog_id/1/',
            ],
            $this->companyDataProvider->getConfigData()
        );
    }

    /**
     * Data provider for testGetData.
     *
     * @return array
     */
    public function getDataDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }
}
