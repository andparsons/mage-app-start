<?php

namespace Magento\AsynchronousOperations\Test\Unit\Model\BulkDescription;

class OptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\AsynchronousOperations\Model\BulkDescription\Options
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $bulkCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userContextMock;

    protected function setUp()
    {
        $this->bulkCollectionFactoryMock = $this->createPartialMock(
            \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory::class,
            ['create']
        );
        $this->userContextMock = $this->createMock(\Magento\Authorization\Model\UserContextInterface::class);
        $this->model = new \Magento\AsynchronousOperations\Model\BulkDescription\Options(
            $this->bulkCollectionFactoryMock,
            $this->userContextMock
        );
    }

    public function testToOptionsArray()
    {
        $userId = 100;
        $collectionMock = $this->createMock(\Magento\AsynchronousOperations\Model\ResourceModel\Bulk\Collection::class);
        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->bulkCollectionFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);

        $this->userContextMock->expects($this->once())->method('getUserId')->willReturn($userId);

        $collectionMock->expects($this->once())->method('getMainTable')->willReturn('table');

        $selectMock->expects($this->once())->method('reset')->willReturnSelf();
        $selectMock->expects($this->once())->method('distinct')->with(true)->willReturnSelf();
        $selectMock->expects($this->once())->method('from')->with('table', ['description'])->willReturnSelf();
        $selectMock->expects($this->once())->method('where')->with('user_id = ?', $userId)->willReturnSelf();

        $itemMock = $this->createPartialMock(
            \Magento\AsynchronousOperations\Model\BulkSummary::class,
            ['getDescription']
        );
        $itemMock->expects($this->exactly(2))->method('getDescription')->willReturn('description');

        $collectionMock->expects($this->once())->method('getSelect')->willReturn($selectMock);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([$itemMock]);

        $expectedResult = [
            [
                'value' => 'description',
                'label' => 'description'
            ]
        ];

        $this->assertEquals($expectedResult, $this->model->toOptionArray());
    }
}
