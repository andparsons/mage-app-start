<?php

namespace Magento\SharedCatalog\Test\Unit\Ui\Component\Product\Listing\Columns;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\SharedCatalog\Api\ProductItemRepositoryInterface;
use Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface;

/**
 * Class SharedCatalogTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SharedCatalogTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uiComponentFactory;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sharedCatalogRepository;

    /**
     * @var ProductItemRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productItemRepositoryInterface;

    /**
     * @var \Magento\SharedCatalog\Ui\Component\Product\Listing\Columns\SharedCatalog
     */
    protected $subject;

    /**
     * Set up
     */
    protected function setUp()
    {
        $processor = $this->createPartialMock(
            \Magento\Framework\View\Element\UiComponent\Processor::class,
            ['register', 'notify']
        );
        $context = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getProcessor']
        );
        $context->expects($this->never())->method('getProcessor')->willReturn($processor);
        $uiComponentFactory = $this->createMock(
            \Magento\Framework\View\Element\UiComponentFactory::class
        );

        $this->searchCriteriaBuilder = $this->getMockForAbstractClass(
            \Magento\Framework\Api\SearchCriteriaBuilder::class,
            [],
            '',
            false,
            false,
            true,
            ['create', 'addFilter']
        );
        $this->searchCriteriaBuilder->expects($this->any())->method('addFilter')->willReturnSelf();
        $searchCriteria = $this->getMockForAbstractClass(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $this->searchCriteriaBuilder->expects($this->any())->method('create')->willReturn($searchCriteria);
        $this->productItemRepositoryInterface = $this->getMockForAbstractClass(
            \Magento\SharedCatalog\Api\ProductItemRepositoryInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getList']
        );
        $this->sharedCatalogRepository = $this->getMockForAbstractClass(
            \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getList']
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->subject = $objectManager->getObject(
            \Magento\SharedCatalog\Ui\Component\Product\Listing\Columns\SharedCatalog::class,
            [
                'context' => $context,
                'uiComponentFactory' => $uiComponentFactory,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
                'productItemRepositoryInterface' => $this->productItemRepositoryInterface,
                'data' => ['name' => 'shared_catalog']
            ]
        );
    }

    /**
     * Test prepareDataSource() method
     *
     * @dataProvider prepareDataSourceDataProvider
     * @param array $dataSource
     * @param array $productItems
     * @param array $expected
     */
    public function testPrepareDataSource($dataSource, $productItems, $sharedCatalogs, $expected)
    {
        $this->buildSharedCatalogItemsMock($sharedCatalogs);
        $this->buildProductItemsMock($productItems);
        $result = $this->subject->prepareDataSource($dataSource);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function prepareDataSourceDataProvider()
    {
        return [
            'empty' => [
                'datasource' => ['data' => ['items' => []]],
                'productItems' => [],
                'sharedCatalogs' => [],
                'expected' => ['data' => ['items' => []]]
            ],
            'p2_not_assigned_to_sc' => [
                'datasource' => [
                    'data' => [
                        'items' => [
                            ['sku' => 'p1'],
                            ['sku' => 'p2'],
                            ['sku' => 'p3']
                        ]
                    ]
                    ],
                'productItems' => [['p1' => 1], ['p1' => 4], ['p3' => 5], ['p4' => 6]], // [sku => customerGroupId]
                'sharedCatalogs' => [1 => 1, 2 => 4, 3 => 5], //[sharedCatalogId => customerGroupId]
                'expected' => [
                    'data' => [
                        'items' => [
                            ['sku' => 'p1', 'shared_catalog' => [1, 2]],
                            ['sku' => 'p2', 'shared_catalog' => ''],
                            ['sku' => 'p3', 'shared_catalog' => [3]]
                        ]
                    ]
                ],
            ],
            'no_assigned_products_to_sc' => [
                'datasource' => [
                    'data' => [
                        'items' => [
                            ['sku' => 'p1'],
                            ['sku' => 'p2'],
                            ['sku' => 'p3']
                        ]
                    ]
                ],
                'productItems' => [], // [sku => customerGroupId]
                'sharedCatalogs' => [1 => 1, 2 => 4, 3 => 5], //[sharedCatalogId => customerGroupId]
                'expected' =>
                    ['data' =>
                        ['items' => [
                            ['sku' => 'p1', 'shared_catalog' => ''],
                            ['sku' => 'p2', 'shared_catalog' => ''],
                            ['sku' => 'p3', 'shared_catalog' => '']
                        ]
                        ]
                    ],
            ],

            'no_products' => [
                'datasource' => [
                    'data' => ['items' => []]
                ],
                'productItems' => [['p1' => 1], ['p1' => 4], ['p3' => 5]], // [sku => customerGroupId]
                'sharedCatalogs' => [1 => 1, 2 => 4, 3 => 5], //[sharedCatalogId => customerGroupId]
                'expected' => [
                    'data' => ['items' => []]
                ],
            ],
        ];
    }

    private function buildProductItemsMock(array $productItems)
    {
        $resultItems = [];
        foreach ($productItems as $productItem) {
            $sku = key($productItem);
            $customerGroupId = $productItem[$sku];
            $item = $this->getMockForAbstractClass(
                \Magento\SharedCatalog\Api\Data\ProductItemInterface::class,
                [],
                '',
                false,
                false,
                true,
                ['getSku', 'getCustomerGroupId']
            );
            $item->expects($this->any())->method('getSku')->willReturn($sku);
            $item->expects($this->any())->method('getCustomerGroupId')->willReturn($customerGroupId);
            $resultItems[] = $item;
        }
        $searchResult = $this->getMockForAbstractClass(
            \Magento\SharedCatalog\Api\Data\ProductItemSearchResultsInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getItems']
        );
        $searchResult->expects($this->any())->method('getItems')->willReturn($resultItems);
        $this->productItemRepositoryInterface->expects($this->any())->method('getList')->willReturn($searchResult);
    }

    private function buildSharedCatalogItemsMock(array $sharedCatalogs)
    {
        $resultItems = [];
        foreach ($sharedCatalogs as $sharedCatalogId => $customerGroupId) {
            $item = $this->getMockForAbstractClass(
                \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class,
                [],
                '',
                false,
                false,
                true,
                ['getId', 'getCustomerGroupId']
            );
            $item->expects($this->any())->method('getId')->willReturn($sharedCatalogId);
            $item->expects($this->any())->method('getCustomerGroupId')->willReturn($customerGroupId);
            $resultItems[] = $item;
        }
        $searchResult = $this->getMockForAbstractClass(
            \Magento\SharedCatalog\Api\Data\SearchResultsInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getItems']
        );
        $searchResult->expects($this->any())->method('getItems')->willReturn($resultItems);
        $this->sharedCatalogRepository->expects($this->any())->method('getList')->willReturn($searchResult);
    }
}
