<?php
namespace Magento\Cms\Test\Unit\Model\ResourceModel\Block\Relation\Store;

use Magento\Cms\Model\ResourceModel\Block;
use Magento\Cms\Model\ResourceModel\Block\Relation\Store\ReadHandler;
use Magento\Framework\EntityManager\MetadataPool;

class ReadHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ReadHandler
     */
    protected $model;

    /**
     * @var Block|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceBlock;

    protected function setUp()
    {
        $this->resourceBlock = $this->getMockBuilder(\Magento\Cms\Model\ResourceModel\Block::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new ReadHandler(
            $this->resourceBlock
        );
    }

    public function testExecute()
    {
        $entityId = 1;
        $storeId = 1;

        $this->resourceBlock->expects($this->once())
            ->method('lookupStoreIds')
            ->willReturn([$storeId]);

        $block = $this->getMockBuilder(\Magento\Cms\Model\Block::class)
            ->disableOriginalConstructor()
            ->getMock();

        $block->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($entityId);
        $block->expects($this->exactly(2))
            ->method('setData')
            ->willReturnMap([
                ['store_id', [$storeId], $block],
                ['stores', [$storeId], $block],
            ]);

        $result = $this->model->execute($block);
        $this->assertInstanceOf(\Magento\Cms\Model\Block::class, $result);
    }

    public function testExecuteWithNoId()
    {
        $block = $this->getMockBuilder(\Magento\Cms\Model\Block::class)
            ->disableOriginalConstructor()
            ->getMock();

        $block->expects($this->once())
            ->method('getId')
            ->willReturn(false);

        $result = $this->model->execute($block);
        $this->assertInstanceOf(\Magento\Cms\Model\Block::class, $result);
    }
}
