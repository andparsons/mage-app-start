<?php
namespace Magento\Customer\Test\Unit\Model;

/**
 * Customer log data logger test.
 */
class LoggerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Customer log data logger.
     *
     * @var \Magento\Customer\Model\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Customer\Model\LogFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logFactory;

    /**
     * Resource instance.
     *
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * DB connection instance.
     *
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->connection = $this->createPartialMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['select', 'insertOnDuplicate', 'fetchRow']
        );
        $this->resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->logFactory = $this->createPartialMock(\Magento\Customer\Model\LogFactory::class, ['create']);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->logger = $objectManagerHelper->getObject(
            \Magento\Customer\Model\Logger::class,
            [
                'resource' => $this->resource,
                'logFactory' => $this->logFactory
            ]
        );
    }

    /**
     * @param int $customerId
     * @param array $data
     * @dataProvider logDataProvider
     * @return void
     */
    public function testLog($customerId, $data)
    {
        $tableName = 'customer_log_table_name';
        $data = array_filter($data);

        if (!$data) {
            $this->expectException('\InvalidArgumentException');
            $this->expectExceptionMessage('Log data is empty');
            $this->logger->log($customerId, $data);
            return;
        }

        $this->resource->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connection);
        $this->resource->expects($this->once())
            ->method('getTableName')
            ->with('customer_log')
            ->willReturn($tableName);
        $this->connection->expects($this->once())
            ->method('insertOnDuplicate')
            ->with($tableName, array_merge(['customer_id' => $customerId], $data), array_keys($data));

        $this->assertEquals($this->logger, $this->logger->log($customerId, $data));
    }

    /**
     * @return array
     */
    public function logDataProvider()
    {
        return [
            [235, ['last_login_at' => '2015-03-04 12:00:00']],
            [235, ['last_login_at' => null]],
        ];
    }

    /**
     * @param int $customerId
     * @param array $data
     * @dataProvider getDataProvider
     * @return void
     */
    public function testGet($customerId, $data)
    {
        $logArguments = [
            'customerId' => $data['customer_id'],
            'lastLoginAt' => $data['last_login_at'],
            'lastLogoutAt' => $data['last_logout_at'],
            'lastVisitAt' => $data['last_visit_at']
        ];

        $select = $this->createMock(\Magento\Framework\DB\Select::class);

        $select->expects($this->any())->method('from')->willReturnSelf();
        $select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $select->expects($this->any())->method('where')->willReturnSelf();
        $select->expects($this->any())->method('order')->willReturnSelf();
        $select->expects($this->any())->method('limit')->willReturnSelf();

        $this->connection->expects($this->any())
            ->method('select')
            ->willReturn($select);

        $this->resource->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connection);
        $this->connection->expects($this->any())
            ->method('fetchRow')
            ->with($select)
            ->willReturn($data);

        $log = $this->getMockBuilder(\Magento\Customer\Model\Log::class)
            ->setConstructorArgs($logArguments)
            ->getMock();

        $this->logFactory->expects($this->any())
            ->method('create')
            ->with($logArguments)
            ->willReturn($log);

        $this->assertEquals($log, $this->logger->get($customerId));
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            [
                235,
                [
                    'customer_id' => 369,
                    'last_login_at' => '2015-03-04 12:00:00',
                    'last_visit_at' => '2015-03-04 12:01:00',
                    'last_logout_at' => '2015-03-04 12:05:00',
                ]
            ],
            [
                235,
                [
                    'customer_id' => 369,
                    'last_login_at' => '2015-03-04 12:00:00',
                    'last_visit_at' => '2015-03-04 12:01:00',
                    'last_logout_at' => null,
                ]
            ],
            [
                235,
                [
                    'customer_id' => null,
                    'last_login_at' => null,
                    'last_visit_at' => null,
                    'last_logout_at' => null,
                ]
            ],
        ];
    }
}
