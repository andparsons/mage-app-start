<?php
namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db\Relation;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ActionPoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Relation\ActionPool
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);

        $entityType = 'Entity_Test';
        $actionName = ['Test_Read' => ['Test_Class']];

        $relationActions = [$entityType => $actionName];
        $this->model = $objectManager->getObject(
            \Magento\Framework\Model\ResourceModel\Db\Relation\ActionPool::class,
            [
                'objectManager' => $this->objectManagerMock,
                'relationActions' => $relationActions
            ]
        );
    }

    public function testGetActionsNoAction()
    {
        $this->assertEmpty($this->model->getActions('test', 'test'));
    }

    public function testGetActions()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Test_Class')
            ->willReturn(new \stdClass());
        $this->assertNotEmpty($this->model->getActions('Entity_Test', 'Test_Read'));
    }
}
