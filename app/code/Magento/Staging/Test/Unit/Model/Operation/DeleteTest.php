<?php
namespace Magento\Staging\Test\Unit\Model\Operation;

use Magento\Framework\EntityManager\Operation\Delete\DeleteAttributes;
use Magento\Framework\EntityManager\Operation\Delete\DeleteMain;
use Magento\Framework\EntityManager\Operation\Delete\DeleteExtensions;
use Magento\Framework\EntityManager\AbstractModelHydrator;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Staging\Model\Operation\Delete;
use Magento\Staging\Model\VersionManager;
use Magento\Framework\EntityManager\Sequence\SequenceManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class DeleteTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $deleteMainMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $deleteExtensionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $deleteRelationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $versionManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sequenceManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityMetadataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityHydrator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $intersectedUpdatesMock;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceConnectionMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterMock;

    /**
     * @var Delete
     */
    protected $delete;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->adapterMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deleteMainMock = $this->getMockBuilder(DeleteMain::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deleteExtensionMock = $this->getMockBuilder(DeleteAttributes::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deleteRelationMock = $this->getMockBuilder(DeleteExtensions::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->versionManagerMock = $this->getMockBuilder(\Magento\Staging\Model\VersionManager\Proxy::class)
            ->disableOriginalConstructor()
            ->setMethods(['isPreviewVersion'])
            ->getMock();
        $this->sequenceManagerMock = $this->getMockBuilder(SequenceManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityMetadataMock = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityHydrator = $this->getMockBuilder(AbstractModelHydrator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->intersectedUpdatesMock = $this->createMock(
            \Magento\Staging\Model\Operation\Delete\UpdateIntersectedUpdates::class
        );
        $typeResolverMock = $this->createMock(\Magento\Framework\EntityManager\TypeResolver::class);
        $typeResolverMock->expects($this->any())
            ->method('resolve')
            ->willReturn(\Magento\Catalog\Api\Data\ProductInterface::class);
        $this->delete = $objectManagerHelper->getObject(
            Delete::class,
            [
                'typeResolver' => $typeResolverMock,
                'metadataPool' => $this->metadataPoolMock,
                'deleteMain' => $this->deleteMainMock,
                'deleteExtension' => $this->deleteExtensionMock,
                'deleteRelation' => $this->deleteRelationMock,
                'versionManager' => $this->versionManagerMock,
                'sequenceManager' => $this->sequenceManagerMock,
                'updateIntersectedUpdates' => $this->intersectedUpdatesMock,
                'resourceConnection' => $this->resourceConnectionMock
            ]
        );
    }

    /**
     * @param bool $isPreview
     * @param array $arguments
     * @param bool $deleteMainProduct
     * @dataProvider dataProviderDelete
     */
    public function testExecute(bool $isPreview, array $arguments, bool $deleteMainProduct)
    {
        $identifierField = 'id';
        $this->resourceConnectionMock->expects($this->any())
            ->method('getConnectionByName')
            ->willReturn($this->adapterMock);
        $this->adapterMock->expects($this->once())
            ->method('beginTransaction');
        $entity = [$identifierField => 1];
        $extracted = [$identifierField => 1];
        $entityType = \Magento\Catalog\Api\Data\ProductInterface::class;
        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($entityType)
            ->willReturn($this->entityMetadataMock);
        $this->metadataPoolMock->expects($this->once())
            ->method('getHydrator')
            ->with($entityType)
            ->willReturn($this->entityHydrator);
        $this->entityHydrator->expects($this->once())
            ->method('extract')
            ->with($entity)
            ->willReturn($extracted);
        $this->entityMetadataMock->expects($deleteMainProduct ? $this->exactly(2) : $this->once())
            ->method('getIdentifierField')
            ->willReturn($identifierField);
        $this->deleteRelationMock->expects($this->once())
            ->method('execute')
            ->with($entity)
            ->willReturn($entity);
        $this->deleteExtensionMock->expects($this->once())
            ->method('execute')
            ->with($entity)
            ->willReturn($entity);
        $this->deleteMainMock->expects($this->once())
            ->method('execute')
            ->with($entity)
            ->willReturn($entity);
        $this->intersectedUpdatesMock->expects($this->once())->method('execute')->with($entity);
        $this->versionManagerMock->expects($this->once())
            ->method('isPreviewVersion')
            ->willReturn($isPreview);
        $this->sequenceManagerMock->expects($deleteMainProduct ? $this->once() : $this->never())
            ->method('delete')
            ->with($entityType, $extracted[$identifierField]);
        $this->assertTrue(
            $this->delete->execute($entity, $arguments)
        );
    }

    /**
     * @return array
     */
    public function dataProviderDelete()
    {
        $arguments = ['created_in' => 1490772480];
        $emptyArguments = [];

        return [
            [
                'isPreview' => false,
                'arguments' => $emptyArguments,
                'delete main product' => true
            ],
            [
                'isPreview' => false,
                'arguments' => $arguments,
                'delete main product' => false
            ],
            [
                'isPreview' => true,
                'arguments' => $arguments,
                'delete main product' => false
            ],
            [
                'isPreview' => true,
                'arguments' => $emptyArguments,
                'delete main product' => false
            ],
        ];
    }
}
