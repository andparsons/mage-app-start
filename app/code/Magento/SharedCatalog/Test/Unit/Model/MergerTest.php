<?php

namespace Magento\SharedCatalog\Test\Unit\Model;

use Magento\AsynchronousOperations\Api\Data\OperationListInterfaceFactory;

/**
 * Unit test for Merger.
 */
class MergerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OperationListInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $operationListFactory;

    /**
     * @var \Magento\Framework\MessageQueue\MergedMessageInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mergedMessageFactory;

    /**
     * @var \Magento\SharedCatalog\Model\Merger
     */
    private $merger;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->operationListFactory = $this
            ->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\OperationListInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->mergedMessageFactory = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\MergedMessageInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->merger = $objectManager->getObject(
            \Magento\SharedCatalog\Model\Merger::class,
            [
                'operationListFactory' => $this->operationListFactory,
                'mergedMessageFactory' => $this->mergedMessageFactory,
            ]
        );
    }

    /**
     * Test for merge().
     *
     * @return void
     */
    public function testMerge()
    {
        $topicName = 'topic.name';
        $messages = [$topicName => [1 => 'message1', 2 => 'message2']];
        $operationList = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\OperationListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->operationListFactory->expects($this->atLeastOnce())->method('create')
            ->with(['items' => $messages[$topicName]])->willReturn($operationList);
        $mergedMessage = $this->getMockBuilder(\Magento\Framework\MessageQueue\MergedMessageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->mergedMessageFactory->expects($this->atLeastOnce())->method('create')->willReturn($mergedMessage);

        $this->assertEquals([$topicName => [$mergedMessage]], $this->merger->merge($messages));
    }
}
