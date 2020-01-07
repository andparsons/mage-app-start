<?php

namespace Magento\Company\Test\Unit\Model\Company\Source;

use Magento\Company\Model\Company\Source\SalesRepresentatives;

/**
 * Class SalesRepresentativesTest.
 */
class SalesRepresentativesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $userCollectionFactory;

    /**
     * @var \Magento\User\Model\ResourceModel\User\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    /**
     * @var \Magento\Company\Model\Company\Source\SalesRepresentatives
     */
    protected $salesRepresentative;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->userCollectionFactory = $this->createPartialMock(
            \Magento\User\Model\ResourceModel\User\CollectionFactory::class,
            [
                'create'
            ]
        );
        $this->collection = $this->createPartialMock(
            \Magento\User\Model\ResourceModel\User\Collection::class,
            [
                'getItems'
            ]
        );
        $this->userCollectionFactory->expects($this->once())->method('create')->willReturn($this->collection);
        $this->salesRepresentative = new SalesRepresentatives($this->userCollectionFactory);
    }

    /**
     * Test toOptionArray
     */
    public function testToOptionArray()
    {
        $id = 666;
        $userName = 'user_name';
        $result = [['label' => $userName, 'value' => $id]];
        $user = $this->createPartialMock(
            \Magento\User\Model\ResourceModel\User::class,
            [
                'getUserName',
                'getId',
            ]
        );
        $this->collection->expects($this->once())->method('getItems')->willReturn([$user]);
        $user->expects($this->once())->method('getUserName')->willReturn($userName);
        $user->expects($this->once())->method('getId')->willReturn($id);
        $this->assertEquals($this->salesRepresentative->toOptionArray(), $result);
    }
}
