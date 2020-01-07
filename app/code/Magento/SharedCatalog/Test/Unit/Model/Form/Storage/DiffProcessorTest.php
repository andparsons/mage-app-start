<?php
namespace Magento\SharedCatalog\Test\Unit\Model\Form\Storage;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Api\CategoryManagementInterface;
use Magento\SharedCatalog\Api\ProductManagementInterface;
use Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\ScheduleBulk;

/**
 * Unit tests for DiffProcessor model.
 */
class DiffProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\DiffProcessor
     */
    private $diffProcessor;

    /**
     * @var \Magento\SharedCatalog\Api\CategoryManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryManagementMock;

    /**
     * @var \Magento\SharedCatalog\Api\ProductManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productManagementMock;

    /**
     * @var ScheduleBulk|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduleBulkMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->categoryManagementMock = $this->getMockBuilder(CategoryManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productManagementMock = $this->getMockBuilder(ProductManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scheduleBulkMock = $this->getMockBuilder(ScheduleBulk::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->diffProcessor = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Model\Form\Storage\DiffProcessor::class,
            [
                'categoryManagement' => $this->categoryManagementMock,
                'productManagement' => $this->productManagementMock,
                'scheduleBulk' => $this->scheduleBulkMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetDiff()
    {
        $sharedCatalogId = 1;
        $result = [
            'pricesChanged' => false,
            'categoriesChanged' => false,
            'productsChanged' => false
        ];
        $categories = [1];
        $products = ['sku1'];
        $prices = [1];

        $storageMock = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()->getMock();
        $this->categoryManagementMock->expects($this->once())->method('getCategories')->with($sharedCatalogId)
            ->willReturn($categories);
        $this->productManagementMock->expects($this->once())->method('getProducts')->with($sharedCatalogId)
            ->willReturn($products);
        $storageMock->expects($this->once())->method('getTierPrices')->willReturn($prices);
        $storageMock->expects($this->once())->method('getUnassignedProductSkus')->willReturn([]);
        $this->scheduleBulkMock->expects($this->once())->method('filterUnchangedPrices')->willReturn([]);
        $storageMock->expects($this->once())->method('getAssignedCategoriesIds')->willReturn($categories);
        $storageMock->expects($this->once())->method('getAssignedProductSkus')->willReturn($products);

        $this->assertEquals($result, $this->diffProcessor->getDiff($storageMock, $sharedCatalogId));
    }
}
