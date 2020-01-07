<?php

namespace Magento\SharedCatalog\Test\Unit\Model\Configure\Category;

use Magento\Catalog\Model\Category;

/**
 * Unit test for Magento\SharedCatalog\Model\Configure\Category\Tree.
 */
class TreeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Tree|\PHPUnit_Framework_MockObject_MockObject
     */
    private $treeResource;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Wizard|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wizardStorage;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\CategoryTree|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryTree;

    /**
     * @var \Magento\SharedCatalog\Model\Configure\Category\Tree
     */
    private $model;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->treeResource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Category\Tree::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->wizardStorage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryTree = $this->getMockBuilder(\Magento\SharedCatalog\Model\ResourceModel\CategoryTree::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\SharedCatalog\Model\Configure\Category\Tree::class,
            [
                'treeResource' => $this->treeResource,
                'categoryTree' => $this->categoryTree
            ]
        );
    }

    /**
     * Test getCategoryRootNode method.
     *
     * @param int $storeId
     * @param int $categoryId
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $counter
     * @return void
     * @dataProvider getCategoryRootNodeDataProvider
     */
    public function testGetCategoryRootNode($storeId, $categoryId, $counter)
    {
        $productSkus = ['sku_1', 'sku_2', 'sku_3'];
        $categoriesIds = [1, 2, 3];
        $level = 0;
        $nodeId = 2;
        $storeGroup = $this->getMockBuilder(\Magento\Store\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tree = $this->getMockBuilder(\Magento\Framework\Data\Tree::class)
            ->disableOriginalConstructor()
            ->setMethods(['addCollectionData', 'getNodeById'])
            ->getMock();
        $collection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Category\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $node = $this->getMockBuilder(\Magento\Framework\Data\Tree\Node::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->treeResource->expects($this->once())->method('load')->willReturn($tree);
        $storeGroup->expects($this->any())->method('getId')->willReturn($storeId);
        $storeGroup->expects($counter)->method('getRootCategoryId')->willReturn($categoryId);
        $this->wizardStorage->expects($this->once())->method('getAssignedProductSkus')->willReturn($productSkus);
        $this->wizardStorage->expects($this->once())->method('getAssignedCategoriesIds')->willReturn($categoriesIds);
        $this->categoryTree
            ->expects($this->once())
            ->method('getCategoryCollection')
            ->with($categoryId, $productSkus)
            ->willReturn($collection);
        $tree->expects($this->once())->method('addCollectionData')->with($collection)->willReturnSelf();
        $tree->expects($this->once())->method('getNodeById')->with($categoryId)->willReturn($node);
        $node->expects($this->exactly(4))
            ->method('getData')
            ->withConsecutive(['level'], ['entity_id'])
            ->willReturnOnConsecutiveCalls($level, $nodeId);
        $node->expects($this->exactly(6))
            ->method('setData')
            ->withConsecutive(['is_checked', true], ['is_active', 1], ['is_checked', false])
            ->willReturnSelf();
        $node->expects($this->exactly(2))->method('getIsActive')->willReturn(2);
        $node->expects($this->exactly(2))
            ->method('getChildren')
            ->willReturnOnConsecutiveCalls(
                new \ArrayIterator([$node]),
                new \ArrayIterator([])
            );

        $this->assertSame($node, $this->model->getCategoryRootNode($this->wizardStorage));
    }

    /**
     * Data provider for getCategoryRootNode method.
     *
     * @return array
     */
    public function getCategoryRootNodeDataProvider()
    {
        return [
            [\Magento\Store\Model\Store::DEFAULT_STORE_ID, Category::TREE_ROOT_ID, $this->never()],
            [2, Category::TREE_ROOT_ID, $this->never()]
        ];
    }
}
