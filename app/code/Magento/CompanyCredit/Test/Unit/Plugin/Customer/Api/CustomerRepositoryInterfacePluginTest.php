<?php

namespace Magento\CompanyCredit\Test\Unit\Plugin\Customer\Api;

/**
 * Test for CustomerRepositoryInterfacePluginTest.
 */
class CustomerRepositoryInterfacePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Model\HistoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\CompanyCredit\Plugin\Customer\Api\CustomerRepositoryInterfacePlugin
     */
    private $customerRepositoryPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->historyRepository = $this->createMock(
            \Magento\CompanyCredit\Model\HistoryRepositoryInterface::class
        );
        $this->searchCriteriaBuilder = $this->createMock(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerRepositoryPlugin = $objectManager->getObject(
            \Magento\CompanyCredit\Plugin\Customer\Api\CustomerRepositoryInterfacePlugin::class,
            [
                'historyRepository' => $this->historyRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
            ]
        );
    }

    /**
     * Test aroundDeleteById method.
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testAroundDeleteById()
    {
        $customerId = 1;
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $this->searchCriteriaBuilder->expects($this->once())->method('addFilter')
            ->with(\Magento\CompanyCredit\Model\HistoryInterface::USER_ID, $customerId)->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $searchResults = $this->createMock(\Magento\Framework\Api\SearchResultsInterface::class);
        $this->historyRepository->expects($this->once())->method('getList')
            ->with($searchCriteria)->willReturn($searchResults);
        $historyItem = $this->createMock(\Magento\CompanyCredit\Model\HistoryInterface::class);
        $searchResults->expects($this->once())->method('getItems')->willReturn(new \ArrayIterator([$historyItem]));
        $historyItem->expects($this->once())->method('setUserId')->with(null)->willReturnSelf();
        $this->historyRepository->expects($this->once())->method('save')->with($historyItem)->willReturn($historyItem);
        $customerRepository = $this->createMock(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $this->assertTrue(
            $this->customerRepositoryPlugin->aroundDeleteById(
                $customerRepository,
                function ($customerId) {
                    return true;
                },
                $customerId
            )
        );
    }
}
