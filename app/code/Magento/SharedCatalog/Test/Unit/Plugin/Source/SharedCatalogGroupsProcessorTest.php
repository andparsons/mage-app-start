<?php

namespace Magento\SharedCatalog\Test\Unit\Plugin\Source;

/**
 * Unit test for SharedCatalogGroupsProcessor plugin.
 */
class SharedCatalogGroupsProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaFactory;

    /**
     * @var \Magento\SharedCatalog\Plugin\Source\SharedCatalogGroupsProcessor
     */
    private $groupsProcessorPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->sharedCatalogRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->searchCriteriaFactory = $this
            ->getMockBuilder(\Magento\Framework\Api\Search\SearchCriteriaFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->groupsProcessorPlugin = $objectManager->getObject(
            \Magento\SharedCatalog\Plugin\Source\SharedCatalogGroupsProcessor::class,
            [
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
                'searchCriteriaFactory' => $this->searchCriteriaFactory,
            ]
        );
    }

    /**
     * Test for prepareGroups method.
     *
     * @return void
     */
    public function testPrepareGroups()
    {
        $groups = [
            [
                'label' => 'Customer Group 1',
                'value' => 1,
            ],
            [
                'label' => 'Customer Group 2',
                'value' => 2,
            ],
        ];
        $customerGroupId = 1;
        $sharedCatalogName = 'Shared Catalog 1';
        $searchCriteria = $this
            ->getMockBuilder(\Magento\Framework\Api\Search\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->searchCriteriaFactory->expects($this->once())->method('create')->willReturn($searchCriteria);
        $searchResults = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\Data\SearchResultsInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->sharedCatalogRepository->expects($this->once())
            ->method('getList')->with($searchCriteria)->willReturn($searchResults);
        $sharedCatalog = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $searchResults->expects($this->once())->method('getItems')->willReturn([$sharedCatalog]);
        $sharedCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $sharedCatalog->expects($this->atLeastOnce())->method('getName')->willReturn($sharedCatalogName);

        $this->assertEquals(
            [
                [
                    'label' => __('Customer Groups'),
                    'value' => [
                        [
                            'label' => 'Customer Group 2',
                            'value' => 2,
                        ]
                    ],
                    '__disableTmpl' => true,
                ],
                [
                    'label' => __('Shared Catalogs'),
                    'value' => [
                        [
                            'label' => $sharedCatalogName,
                            'value' => 1,
                            '__disableTmpl' => true,
                        ]
                    ],
                    '__disableTmpl' => true,
                ],
            ],
            $this->groupsProcessorPlugin->prepareGroups($groups)
        );
    }

    /**
     * Test for prepareGroups method with empty groups list.
     *
     * @return void
     */
    public function testPrepareGroupsWithEmptyList()
    {
        $groups = [];
        $this->assertEquals([], $this->groupsProcessorPlugin->prepareGroups($groups));
    }
}
