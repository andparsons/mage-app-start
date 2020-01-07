<?php

namespace Magento\RequisitionList\Test\Unit\Model\RequisitionListItem;

/**
 * Unit test for ProductExtractor model.
 */
class ProductExtractorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Api\ProductCustomOptionRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productOptionRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\ProductExtractor
     */
    private $productExtractor;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->productOptionRepository = $this
            ->getMockBuilder(\Magento\Catalog\Api\ProductCustomOptionRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->productExtractor = $objectManager->getObject(
            \Magento\RequisitionList\Model\RequisitionListItem\ProductExtractor::class,
            [
                'productRepository' => $this->productRepository,
                'productOptionRepository' => $this->productOptionRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
            ]
        );
    }

    /**
     * Test for extract method.
     *
     * @return void
     */
    public function testExtract()
    {
        $productSku = 'SKU01';
        $websiteId = 1;

        $this->searchCriteriaBuilder->expects($this->exactly(2))->method('addFilter')
            ->withConsecutive(
                [\Magento\Catalog\Api\Data\ProductInterface::SKU, [$productSku], 'in'],
                ['website_id', $websiteId, 'in']
            )->wilLReturnSelf();
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $searchResults = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductSearchResultsInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->productRepository->expects($this->once())
            ->method('getList')->with($searchCriteria)->willReturn($searchResults);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['getProductOptionsCollection', 'addOption', 'getSku'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $searchResults->expects($this->once())->method('getItems')->willReturn([$product]);
        $option = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductOptionInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->productOptionRepository->expects($this->once())
            ->method('getProductOptions')->with($product)->willReturn([$option]);
        $product->expects($this->once())->method('setOptions')->with([$option])->willReturnSelf();
        $product->expects($this->once())->method('getSku')->willReturn($productSku);
        $this->assertEquals(
            [$productSku => $product],
            $this->productExtractor->extract([$productSku], $websiteId)
        );
    }
}
