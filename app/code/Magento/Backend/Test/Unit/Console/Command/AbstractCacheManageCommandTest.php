<?php

namespace Magento\Backend\Test\Unit\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractCacheManageCommandTest extends AbstractCacheCommandTest
{
    /** @var  string */
    protected $cacheEventName;

    /** @var  \Magento\Framework\Event\ManagerInterface | \PHPUnit_Framework_MockObject_MockObject */
    protected $eventManagerMock;

    protected function setUp()
    {
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        parent::setUp();
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            'implicit all' => [
                [],
                ['A', 'B', 'C'],
                $this->getExpectedExecutionOutput(['A', 'B', 'C']),
            ],
            'specified types' => [
                ['types' => ['A', 'B']],
                ['A', 'B'],
                $this->getExpectedExecutionOutput(['A', 'B']),
            ],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The following requested cache types are not supported:
     */
    public function testExecuteInvalidCacheType()
    {
        $this->cacheManagerMock->expects($this->once())->method('getAvailableTypes')->willReturn(['A', 'B', 'C']);
        $param = ['types' => ['A', 'D']];
        $commandTester = new CommandTester($this->command);
        $commandTester->execute($param);
    }
}
