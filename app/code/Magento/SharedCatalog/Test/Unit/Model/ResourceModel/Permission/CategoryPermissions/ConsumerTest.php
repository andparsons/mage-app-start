<?php

namespace Magento\SharedCatalog\Test\Unit\Model\ResourceModel\Permission\CategoryPermissions;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationListInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Model\Permissions\Synchronizer;
use Magento\SharedCatalog\Model\ResourceModel\Permission\CategoryPermissions\Consumer;
use Magento\SharedCatalog\Model\SharedCatalogInvalidation;
use Psr\Log\LoggerInterface;

/**
 * Test for category permissions consumer.
 */
class ConsumerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManager;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var Synchronizer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissionsSynchronizer;

    /**
     * @var SharedCatalogInvalidation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogInvalidation;

    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->permissionsSynchronizer = $this->createMock(Synchronizer::class);
        $this->sharedCatalogInvalidation = $this->createMock(SharedCatalogInvalidation::class);

        $objectManager = new ObjectManagerHelper($this);
        $this->consumer = $objectManager->getObject(
            Consumer::class,
            [
                'logger' => $this->logger,
                'entityManager' => $this->entityManager,
                'serializer' => $this->serializer,
                'permissionsSynchronizer' => $this->permissionsSynchronizer,
                'sharedCatalogInvalidation' => $this->sharedCatalogInvalidation,
            ]
        );
    }

    /**
     * Test for processOperations method.
     *
     * @return void
     */
    public function testProcessOperations()
    {
        $data = [
            'category_id' => 1,
            'group_ids' => '2,3',
        ];

        $operation = $this->getMockBuilder(OperationInterface::class)
            ->disableOriginalConstructor()->getMock();
        $operationList = $this->getMockBuilder(OperationListInterface::class)
            ->disableOriginalConstructor()->getMock();
        $operationList->expects($this->atLeastOnce())->method('getItems')->willReturn([$operation]);
        $operation->expects($this->once())->method('getSerializedData')->willReturn(json_encode($data));
        $this->serializer->expects($this->once())->method('unserialize')->with(json_encode($data))->willReturn($data);
        $this->permissionsSynchronizer->expects($this->once())
            ->method('updateCategoryPermissions')->with($data['category_id'], explode(',', $data['group_ids']));
        $this->sharedCatalogInvalidation->expects($this->once())
            ->method('reindexCatalogPermissions')->with([$data['category_id']]);
        $operation->expects($this->once())->method('setStatus')
            ->with(OperationInterface::STATUS_TYPE_COMPLETE)
            ->willReturnSelf();
        $operation->expects($this->once())->method('setResultMessage')->with(null)->willReturnSelf();
        $this->entityManager->expects($this->once())->method('save')->with($operationList)->willReturn($operationList);
        $this->consumer->processOperations($operationList);
    }
}
