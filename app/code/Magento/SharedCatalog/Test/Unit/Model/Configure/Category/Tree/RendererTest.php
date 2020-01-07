<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Test\Unit\Model\Configure\Category\Tree;

/**
 * Test for model Configure\Category\Tree\Renderer.
 */
class RendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\Configure\Category\Tree\Renderer
     */
    protected $rendererMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->rendererMock = $objectManager->getObject(
            \Magento\SharedCatalog\Model\Configure\Category\Tree\Renderer::class
        );
    }

    /**
     * Test render method.
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

        $node = $this->prepareNodeMock($item, $level, $rootProductCount, $productCount);
        $childNode = $this->prepareNodeMock($item['children'][0], $level, $rootProductCount, $productCount);
        $childNode->expects($this->once())->method('getChildren')->willReturn(new \ArrayIterator([]));
        $node->expects($this->once())->method('getChildren')->willReturn(new \ArrayIterator([$childNode]));
        $this->assertEquals($item, $this->rendererMock->render($node));
    }

    /**
     * Prepare Tree Node Mock object.
     *
     * @param array $item
     * @param int $level
     * @param int $rootProductCount
     * @param int $productCount
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function prepareNodeMock(array $item, $level, $rootProductCount, $productCount)
    {
        $node = $this->getMockBuilder(\Magento\Framework\Data\Tree\Node::class)
            ->disableOriginalConstructor()
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
            ->getMock();

        $nodeId = $item['data']['id'];
        $nodeName = $item['data']['name'];
        $node->expects($this->once())->method('getId')->willReturn($nodeId);
        $node->expects($this->exactly(3))->method('getName')->willReturn($nodeName);
        $node->expects($this->once())->method('getIsChecked')->willReturn($item['data']['is_checked']);
        $node->expects($this->once())->method('getIsActive')->willReturn($item['data']['is_active']);
        $node->expects($this->exactly(2))->method('getLevel')->willReturn($level);
        $node->expects($this->exactly($rootProductCount))->method('getRootProductCount')->willReturn(0);
        $node->expects($this->exactly($rootProductCount))->method('getRootSelectedCount')->willReturn(0);
        $node->expects($this->exactly($productCount))->method('getProductCount')
            ->willReturn($item['data']['product_count']);
        $node->expects($this->exactly($productCount))->method('getSelectedCount')
            ->willReturn($item['data']['product_assigned']);
        $node->expects($this->once())->method('hasChildren')->willReturn(true);

        return $node;
    }

    /**
     * Data provider for render method.
     *
     * @return array
     */
    public function renderDataProvider()
    {
        $nodeChildren = [
            [
                'text' => 'Child Node Name',
                'a_attr' => ['data-category-name' => 'Child Node Name'],
                'data' => [
                    'id' => 15,
                    'name' => 'Child Node Name',
                    'product_count' => 0,
                    'product_assigned' => 0,
                    'is_checked' => 1,
                    'is_active' => 1,
                    'is_opened' => 0
                ],
                'children' => []
            ]
        ];

        return [
            [
                [
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
                    'children' => $nodeChildren
                ],
                1,
                1,
                0
            ],
            [
                [
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
                    'children' => $nodeChildren
                ],
                3,
                0,
                1
            ]
        ];
    }
}
