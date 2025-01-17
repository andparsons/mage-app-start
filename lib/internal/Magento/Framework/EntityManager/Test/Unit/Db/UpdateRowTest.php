<?php
namespace Magento\Framework\EntityManager\Test\Unit\Db;

use Magento\Framework\EntityManager\Db\UpdateRow;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;

/**
 * Class UpdateRowTest
 */
class UpdateRowTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UpdateRow
     */
    protected $model;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceConnectionMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var EntityMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataMock;

    protected function setUp()
    {
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataMock = $this->getMockBuilder(EntityMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        $this->metadataMock = $this->getMockBuilder(EntityMetadataInterface::class)
            ->getMockForAbstractClass();

        $this->model = (new ObjectManager($this))->getObject(UpdateRow::class, [
            'metadataPool' => $this->metadataPoolMock,
            'resourceConnection' => $this->resourceConnectionMock,
        ]);
    }

    /**
     * @dataProvider columnsDataProvider
     * @param array $data
     * @param array $columns
     * @param array $preparedColumns
     */
    public function testExecute(array $data, array $columns, array $preparedColumns)
    {
        $primaryKeyName = 'entity_id';
        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with('test')
            ->willReturn($this->metadataMock);
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnectionByName')
            ->willReturn($this->connectionMock);
        $this->metadataMock->expects($this->once())
            ->method('getEntityConnectionName')
            ->willReturn('test_connection_name');
        $this->metadataMock->expects($this->atLeastOnce())
            ->method('getEntityTable')
            ->willReturn('test_entity_table');
        $this->connectionMock->expects($this->once())
            ->method('update')
            ->with('test_entity_table', $preparedColumns, ['test_link_field' . ' = ?' => $data['test_link_field']]);
        $this->connectionMock->expects($this->once())->method('getIndexList')
            ->willReturn([$primaryKeyName => ['COLUMNS_LIST' => ['test_link_field']]]);
        $this->connectionMock->expects($this->once())->method('getPrimaryKeyName')
            ->willReturn($primaryKeyName);
        $this->connectionMock->expects($this->once())
            ->method('describeTable')
            ->willReturn($columns);
        $this->metadataMock->expects($this->exactly(2))
            ->method('getIdentifierField')
            ->willReturn('test_identified_field');
        if (empty($data['updated_at'])) {
            unset($data['updated_at']);
        }
        $this->assertSame($data, $this->model->execute('test', $data));
    }

    /**
     * @return array
     */
    public function columnsDataProvider()
    {
        $data = [
            'test_link_field' => 1,
            'identified_field' => 'test_identified_field',
            'test_simple' => 'test_value',
        ];
        $columns = [
            'test_nullable' => [
                'NULLABLE' => true,
                'DEFAULT' => false,
                'IDENTITY' => false,
                'COLUMN_NAME' => 'test_nullable',
            ],
            'test_simple' => [
                'NULLABLE' => true,
                'DEFAULT' => false,
                'IDENTITY' => false,
                'COLUMN_NAME' => 'test_simple',
            ],
        ];
        $preparedColumns = [
            'test_identified_field' => null,
            'test_nullable' => null,
            'test_simple' => 'test_value',
        ];

        return [
            'default' => [
                'data' => $data,
                'columns' => $columns,
                'preparedColumns' => $preparedColumns,
            ],
            'empty timestamp field' => [
                'data' => array_merge($data, ['updated_at' => '']),
                'columns' => array_merge(
                    $columns,
                    [
                        'updated_at' => [
                            'NULLABLE' => false,
                            'DEFAULT' => 'CURRENT_TIMESTAMP',
                            'IDENTITY' => false,
                            'COLUMN_NAME' => 'updated_at',
                        ],
                    ]
                ),
                'preparedColumns' => $preparedColumns,
            ],
            'filled timestamp field' => [
                'data' => array_merge($data, ['updated_at' => '2016-01-01 00:00:00']),
                'columns' => array_merge(
                    $columns,
                    [
                        'updated_at' => [
                            'NULLABLE' => false,
                            'DEFAULT' => 'CURRENT_TIMESTAMP',
                            'IDENTITY' => false,
                            'COLUMN_NAME' => 'updated_at',
                        ],
                    ]
                ),
                'preparedColumns' => array_merge($preparedColumns, ['updated_at' => '2016-01-01 00:00:00']),
            ],
        ];
    }
}
