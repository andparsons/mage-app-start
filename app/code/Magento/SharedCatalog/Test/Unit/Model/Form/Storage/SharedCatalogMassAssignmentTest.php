<?php
namespace Magento\SharedCatalog\Test\Unit\Model\Form\Storage;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for SharedCatalogMassAssignment.
 */
class SharedCatalogMassAssignmentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\Price\ProductTierPriceLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productTierPriceLoader;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogAssignment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogAssignment;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\SharedCatalogMassAssignment
     */
    private $sharedCatalogMassAssignment;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->productTierPriceLoader = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Price\ProductTierPriceLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalogAssignment = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalogAssignment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->sharedCatalogMassAssignment = $objectManagerHelper->getObject(
            \Magento\SharedCatalog\Model\Form\Storage\SharedCatalogMassAssignment::class,
            [
                'productTierPriceLoader' => $this->productTierPriceLoader,
                'sharedCatalogAssignment' => $this->sharedCatalogAssignment,
            ]
        );
    }

    /**
     * Unit test for assign().
     *
     * @return void
     */
    public function testAssign()
    {
        $sku = 'sku';
        $categoryIds = [2];
        $storage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $collection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->setMethods(['setPageSize', 'getLastPageNumber', 'setCurPage', 'addCategoryIds', 'getItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->atLeastOnce())->method('setPageSize')->willReturnSelf();
        $collection->expects($this->atLeastOnce())->method('getLastPageNumber')->willReturn(1);
        $collection->expects($this->atLeastOnce())->method('setCurPage')->with(1)->willReturnSelf();
        $collection->expects($this->atLeastOnce())->method('addCategoryIds')->willReturnSelf();
        $collection->expects($this->atLeastOnce())->method('getItems')->willReturn([$product]);
        $storage->expects($this->atLeastOnce())->method('assignProducts')->with([$sku]);
        $this->sharedCatalogAssignment->expects($this->atLeastOnce())->method('getAssignCategoryIdsByProducts')
            ->with([$product])->willReturn($categoryIds);
        $this->sharedCatalogAssignment->expects($this->atLeastOnce())->method('getAssignProductsSku')
            ->with([$product])->willReturn([$sku]);
        $storage->expects($this->atLeastOnce())->method('assignCategories')->with($categoryIds);
        $this->productTierPriceLoader->expects($this->atLeastOnce())->method('populateProductTierPrices')
            ->with([$product], 1, $storage);

        $this->sharedCatalogMassAssignment->assign($collection, $storage, 1, true);
    }

    /**
     * Unit test for assign() for products unassign action.
     *
     * @return void
     */
    public function testAssignProductsUnassignAction()
    {
        $sku = 'sku';
        $storage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $collection = $this->getMockBuilder(\Magento\Eav\Model\Entity\Collection\AbstractCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->atLeastOnce())->method('setPageSize')->willReturnSelf();
        $collection->expects($this->atLeastOnce())->method('getLastPageNumber')->willReturn(1);
        $collection->expects($this->atLeastOnce())->method('setCurPage')->with(1)->willReturnSelf();
        $collection->expects($this->atLeastOnce())->method('getItems')->willReturn([$product]);
        $this->sharedCatalogAssignment->expects($this->atLeastOnce())->method('getAssignProductsSku')
            ->with([$product])->willReturn([$sku]);
        $storage->expects($this->atLeastOnce())->method('unassignProducts')->with([$sku]);
        $this->productTierPriceLoader->expects($this->atLeastOnce())->method('populateProductTierPrices')
            ->with([$product], 1, $storage);

        $this->sharedCatalogMassAssignment->assign($collection, $storage, 1, false);
    }
}
