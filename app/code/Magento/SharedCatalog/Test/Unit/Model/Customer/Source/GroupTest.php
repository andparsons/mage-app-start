<?php

namespace Magento\SharedCatalog\Test\Unit\Model\Customer\Source;

/**
 * Unit test for Group.
 */
class GroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupCollectionFactory;

    /**
     * @var \Magento\SharedCatalog\Model\Customer\Source\Group
     */
    private $groupSource;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->groupCollectionFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Customer\Source\Collection\GroupFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->groupSource = $objectManager->getObject(
            \Magento\SharedCatalog\Model\Customer\Source\Group::class,
            [
                'groupCollectionFactory' => $this->groupCollectionFactory,
            ]
        );
    }

    /**
     * Test for method toOptionArray.
     *
     * @return void
     */
    public function testToOptionArray()
    {
        $groupData = [
            ['id' => 1, 'code' => 'Group 1'],
            ['id' => 2, 'code' => 'Shared Catalog 2'],
        ];
        $groups = [
            $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
                ->disableOriginalConstructor()
                ->setMethods(['getSharedCatalogName'])
                ->getMockForAbstractClass(),
            $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
                ->disableOriginalConstructor()
                ->setMethods(['getSharedCatalogName'])
                ->getMockForAbstractClass(),
        ];
        $groups[0]->expects($this->once())->method('getId')->willReturn($groupData[0]['id']);
        $groups[0]->expects($this->once())->method('getCode')->willReturn($groupData[0]['code']);
        $groups[1]->expects($this->once())->method('getId')->willReturn($groupData[1]['id']);
        $groups[1]->expects($this->once())->method('getSharedCatalogName')->willReturn($groupData[1]['code']);
        $collection = $this->getMockBuilder(\Magento\SharedCatalog\Model\Customer\Source\Collection\Group::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->atLeastOnce())->method('joinSharedCatalogTable')->willReturnSelf();
        $collection->expects($this->exactly(2))->method('getIterator')->willReturnOnConsecutiveCalls(
            new \ArrayIterator([$groups[0]]),
            new \ArrayIterator([$groups[1]])
        );
        $this->groupCollectionFactory->expects($this->atLeastOnce())->method('create')->willReturn($collection);

        $this->assertEquals(
            [
                [
                    'label' => __('Customer Groups'),
                    'value' => [
                        [
                            'label' => $groupData[0]['code'],
                            'value' => $groupData[0]['id'],
                            '__disableTmpl' => true,
                        ],
                    ],
                ],
                [
                    'label' => __('Shared Catalogs'),
                    'value' => [
                        [
                            'label' => $groupData[1]['code'],
                            'value' => $groupData[1]['id'],
                            '__disableTmpl' => true,
                        ],
                    ],
                ],
            ],
            $this->groupSource->toOptionArray()
        );
    }
}
