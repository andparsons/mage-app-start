<?php

namespace Magento\Company\Test\Unit\Model\Customer\Source;

use Magento\Customer\Api\Data\GroupInterface;

/**
 * Class GroupTest.
 */
class GroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\SortOrderBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sortOrderBuilder;

    /**
     * @var \Magento\Company\Model\Customer\Source\Group
     */
    private $customerGroupSource;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->groupRepository = $this->createMock(
            \Magento\Customer\Api\GroupRepositoryInterface::class
        );
        $this->searchCriteriaBuilder = $this->createMock(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
        $this->sortOrderBuilder = $this->createMock(
            \Magento\Framework\Api\SortOrderBuilder::class
        );

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerGroupSource = $objectManagerHelper->getObject(
            \Magento\Company\Model\Customer\Source\Group::class,
            [
                'groupRepository' => $this->groupRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'sortOrderBuilder' => $this->sortOrderBuilder,
            ]
        );
    }

    /**
     * Test for toOptionArray method.
     *
     * @return void
     */
    public function testToOptionArray()
    {
        $groupId = 1;
        $groupName = 'Group Name';

        $customerGroup = $this->createMock(\Magento\Customer\Model\Group::class);
        $sortOrder = $this->createMock(\Magento\Framework\Api\SortOrder::class);
        $searchResults = $this->createMock(
            \Magento\Customer\Api\Data\GroupSearchResultsInterface::class
        );
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $this->sortOrderBuilder->expects($this->once())
            ->method('setField')->with(GroupInterface::CODE)->willReturnSelf();
        $this->sortOrderBuilder->expects($this->once())->method('setAscendingDirection')->willReturnSelf();
        $this->sortOrderBuilder->expects($this->once())->method('create')->willReturn($sortOrder);
        $this->searchCriteriaBuilder->expects($this->once())->method('addFilter')
            ->with(GroupInterface::ID, GroupInterface::NOT_LOGGED_IN_ID, 'neq')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addSortOrder')->with($sortOrder)->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $this->groupRepository->expects($this->once())
            ->method('getList')->with($searchCriteria)->willReturn($searchResults);
        $searchResults->expects($this->once())->method('getItems')->willReturn(new \ArrayIterator([$customerGroup]));
        $customerGroup->expects($this->once())->method('getId')->willReturn($groupId);
        $customerGroup->expects($this->once())->method('getCode')->willReturn($groupName);

        $this->assertEquals(
            [['label' => $groupName, 'value' => $groupId]],
            $this->customerGroupSource->toOptionArray()
        );
    }
}
