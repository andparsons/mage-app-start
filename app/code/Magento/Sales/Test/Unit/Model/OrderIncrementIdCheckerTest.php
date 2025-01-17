<?php
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

/**
 * Unit test for \Magento\Sales\Model\OrderIncrementIdChecker.
 */
class OrderIncrementIdCheckerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\OrderIncrementIdChecker
     */
    private $model;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $this->selectMock->expects($this->any())->method('where');

        $this->adapterMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $this->adapterMock->expects($this->any())->method('select')->will($this->returnValue($this->selectMock));

        $this->resourceMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Order::class);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->adapterMock);

        $this->model = $objectManagerHelper->getObject(
            \Magento\Sales\Model\OrderIncrementIdChecker::class,
            [
                'resourceModel' => $this->resourceMock,
            ]
        );
    }

    /**
     * Unit test to verify if isOrderIncrementIdUsed method works with different types increment ids.
     *
     * @param string|int $value
     * @return void
     * @dataProvider isOrderIncrementIdUsedDataProvider
     */
    public function testIsIncrementIdUsed($value): void
    {
        $expectedBind = [':increment_id' => $value];
        $this->adapterMock->expects($this->once())->method('fetchOne')->with($this->selectMock, $expectedBind);
        $this->model->isIncrementIdUsed($value);
    }

    /**
     * @return array
     */
    public function isOrderIncrementIdUsedDataProvider(): array
    {
        return [[100000001], ['10000000001'], ['M10000000001']];
    }
}
