<?php

namespace Magento\Sales\Test\Unit\Model\CronJob;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\CronJob\CleanExpiredOrders;

class CleanExpiredOrdersTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storesConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderManagementMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var CleanExpiredOrders
     */
    protected $model;

    protected function setUp()
    {
        $this->storesConfigMock = $this->createMock(\Magento\Store\Model\StoresConfig::class);
        $this->collectionFactoryMock = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\CollectionFactory::class,
            ['create']
        );
        $this->orderCollectionMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
        $this->orderManagementMock = $this->createMock(\Magento\Sales\Api\OrderManagementInterface::class);

        $this->model = new CleanExpiredOrders(
            $this->storesConfigMock,
            $this->collectionFactoryMock,
            $this->orderManagementMock
        );
    }

    public function testExecute()
    {
        $schedule = [
            0 => 300,
            1 => 20,
        ];
        $this->storesConfigMock->expects($this->once())
            ->method('getStoresConfigByPath')
            ->with('sales/orders/delete_pending_after')
            ->willReturn($schedule);
        $this->collectionFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->orderCollectionMock);
        $this->orderCollectionMock->expects($this->exactly(2))
            ->method('getAllIds')
            ->willReturn([1, 2]);
        $this->orderCollectionMock->expects($this->exactly(4))->method('addFieldToFilter');
        $this->orderManagementMock->expects($this->exactly(4))->method('cancel');

        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $selectMock->expects($this->exactly(2))->method('where')->willReturnSelf();
        $this->orderCollectionMock->expects($this->exactly(2))->method('getSelect')->willReturn($selectMock);

        $this->model->execute();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Error500
     */
    public function testExecuteWithException()
    {
        $schedule = [
            1 => 20,
        ];
        $exceptionMessage = 'Error500';

        $this->storesConfigMock->expects($this->once())
            ->method('getStoresConfigByPath')
            ->with('sales/orders/delete_pending_after')
            ->willReturn($schedule);
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->orderCollectionMock);
        $this->orderCollectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn([1]);
        $this->orderCollectionMock->expects($this->exactly(2))->method('addFieldToFilter');
        $this->orderManagementMock->expects($this->once())->method('cancel');

        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $selectMock->expects($this->once())->method('where')->willReturnSelf();
        $this->orderCollectionMock->expects($this->once())->method('getSelect')->willReturn($selectMock);

        $this->orderManagementMock->expects($this->once())
            ->method('cancel')
            ->willThrowException(new \Exception($exceptionMessage));

        $this->model->execute();
    }
}
