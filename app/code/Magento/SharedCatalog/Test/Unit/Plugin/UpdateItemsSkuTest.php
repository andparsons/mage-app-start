<?php

namespace Magento\SharedCatalog\Test\Unit\Plugin;

/**
 * Unit test for \Magento\SharedCatalog\Plugin\UpdateItemsSku.
 */
class UpdateItemsSkuTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogProductItemRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteria;

    /**
     * @var \Magento\SharedCatalog\Plugin\UpdateItemsSku
     */
    private $productPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'create'])
            ->disableOriginalConstructor()->getMock();

        $this->sharedCatalogProductItemRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\ProductItemRepository::class)
            ->setMethods(['getList', 'save'])
            ->disableOriginalConstructor()->getMock();

        $this->searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->productPlugin = $objectManager->getObject(
            \Magento\SharedCatalog\Plugin\UpdateItemsSku::class,
            [
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'sharedCatalogProductItemRepository' => $this->sharedCatalogProductItemRepository
            ]
        );
    }

    /**
     * Test for afterSave().
     *
     * @param string $sku
     * @param string $origSku
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $call
     * @return void
     * @dataProvider afterSaveDataProvider
     */
    public function testAfterSave($sku, $origSku, $call)
    {
        $subject = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())->method('getOrigData')->with('sku')->willReturn($sku);
        $product->expects($this->atLeastOnce())->method('getSku')->willReturn($origSku);
        $this->searchCriteriaBuilder->expects($call)->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($call)->method('create')->willReturn($this->searchCriteria);
        $sharedCatalogProductSearchResults = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemSearchResultsInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $productItem = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemInterface::class)
            ->setMethods(['setSku'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sharedCatalogProductSearchResults
            ->expects($call)->method('getItems')
            ->willReturn([$productItem]);
        $this->sharedCatalogProductItemRepository
            ->expects($call)->method('getList')
            ->willReturn($sharedCatalogProductSearchResults);
        $productItem->expects($call)->method('setSku')->willReturnSelf();
        $this->sharedCatalogProductItemRepository
            ->expects($call)->method('getList')
            ->willReturn($sharedCatalogProductSearchResults);
        $this->assertEquals($product, $this->productPlugin->afterSave($subject, $product));
    }

    /**
     * Data provider for afterSave() test.
     *
     * @return array
     */
    public function afterSaveDataProvider()
    {
        return [
            ['test_sku_1', 'test_sku_1', $this->never()],
            ['test_sku_1', 'origSku' => 'test_orig_sku_1', $this->atLeastOnce()]
        ];
    }
}
