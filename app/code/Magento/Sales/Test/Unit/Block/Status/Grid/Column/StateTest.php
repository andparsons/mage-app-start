<?php

namespace Magento\Sales\Test\Unit\Block\Status\Grid\Column;

class StateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var  \Magento\Sales\Block\Status\Grid\Column\State
     */
    private $stateColumn;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderStatusCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->orderStatusCollectionFactoryMock = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory::class,
            ['create']
        );
        $this->configMock = $helper->getObject(
            \Magento\Sales\Model\Order\Config::class,
            [
                'orderStatusCollectionFactory' => $this->orderStatusCollectionFactoryMock
            ]
        );
        $this->stateColumn = $helper
            ->getObject(
                \Magento\Sales\Block\Status\Grid\Column\State::class,
                [
                    'config' => $this->configMock,
                ]
            );
    }

    public function testDecorateState()
    {
        $rowMock = $this->createPartialMock(\Magento\Sales\Model\Order\Status::class, ['getStatus']);
        $rowMock->expects($this->any())->method('getStatus')->willReturn('fraud');
        $columnMock = $this->createMock(\Magento\Backend\Block\Widget\Grid\Column::class);
        $statuses = [
            new \Magento\Framework\DataObject(
                [
                    'status' => 'fraud',
                    'state' => 'processing',
                    'label' => 'Suspected Fraud',
                ]
            ),
            new \Magento\Framework\DataObject(
                [
                    'status' => 'processing',
                    'state' => 'processing',
                    'label' => 'Processing',
                ]
            )
        ];
        $collectionMock = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\Collection::class,
            ['create', 'joinStates']
        );
        $this->orderStatusCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())
            ->method('joinStates')
            ->will($this->returnValue($statuses));

        $result = $this->stateColumn->decorateState('processing', $rowMock, $columnMock, false);
        $this->assertSame('processing[Suspected Fraud]', $result);
    }
}
