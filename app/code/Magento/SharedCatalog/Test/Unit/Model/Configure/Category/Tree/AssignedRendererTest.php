<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Test\Unit\Model\Configure\Category\Tree;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Model\Configure\Category\Tree\AssignedRenderer;

/**
 * Test for model Configure\Category\Tree\AssignedRenderer.
 */
class AssignedRendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var AssignedRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assignedRenderer;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->assignedRenderer = $this->objectManagerHelper->getObject(AssignedRenderer::class);
    }

    /**
     * Test for render().
     *
     * @param array $item
     * @param int $level
     * @param int $rootProductCount
     * @param int $productCount
     * @return void
     * @dataProvider renderDataProvider
     */
    public function testRender(
        array $item,
        $level,
        $rootProductCount,
        $productCount
    ) {
        $nodeId = 14;
        $nodeName = 'Node Name';
        $node = $this->getMockBuilder(\Magento\Framework\Data\Tree\Node::class)
            ->setMethods(
                [
                    'getIsActive',
                    'getId',
                    'getName',
                    'getIsChecked',
                    'hasChildren',
                    'getLevel',
                    'getRootProductCount',
                    'getProductCount',
                    'getRootSelectedCount',
                    'getSelectedCount',
                    'getChildren'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $node->expects($this->exactly(2))->method('getId')->willReturn($nodeId);
        $node->expects($this->exactly(6))->method('getName')->willReturn($nodeName);
        $node->expects($this->exactly(3))->method('getIsChecked')->willReturn(1);
        $node->expects($this->exactly(2))->method('getIsActive')->willReturn(1);
        $node->expects($this->exactly(4))->method('getLevel')->willReturn($level);
        $node->expects($this->exactly($rootProductCount))->method('getRootProductCount')->willReturn(0);
        $node->expects($this->exactly($rootProductCount))->method('getRootSelectedCount')->willReturn(0);
        $node->expects($this->exactly($productCount))->method('getProductCount')->willReturn(0);
        $node->expects($this->exactly($productCount))->method('getSelectedCount')->willReturn(0);
        $node->expects($this->exactly(2))->method('hasChildren')->willReturn(true);
        $node->expects($this->exactly(2))->method('getChildren')
            ->willReturnOnConsecutiveCalls(new \ArrayIterator([$node]), new \ArrayIterator([]));

        $this->assertEquals($item, $this->assignedRenderer->render($node));
    }

    /**
     * Data provider for render() test.
     *
     * @return array
     */
    public function renderDataProvider()
    {
        $item = [
            'text' => 'Node Name',
            'a_attr' => ['data-category-name' => 'Node Name'],
            'data' => [
                'id' => 14,
                'name' => 'Node Name',
                'product_count' => 0,
                'product_assigned' => 0,
                'is_checked' => 1,
                'is_active' => 1,
                'is_opened' => 0
            ],
            'children' => [
                0 => [
                    'text' => 'Node Name',
                    'a_attr' => ['data-category-name' => 'Node Name'],
                    'data' => [
                        'id' => 14,
                        'name' => 'Node Name',
                        'product_count' => 0,
                        'product_assigned' => 0,
                        'is_checked' => 1,
                        'is_active' => 1,
                        'is_opened' => 0
                    ],
                    'children' => []
                ]
            ]
        ];
        return [
            [$item, 1, 2, 0]
        ];
    }
}
