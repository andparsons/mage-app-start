<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Unit\Model;

/**
 * Unit test for ProductSkuLocator model.
 */
class ProductSkuLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\RequisitionList\Model\ProductSkuLocator
     */
    private $productSkuLocator;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilder = $this
            ->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->productSkuLocator = $objectManager->getObject(
            \Magento\RequisitionList\Model\ProductSkuLocator::class,
            [
                'productRepository' => $this->productRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
            ]
        );
    }

    /**
     * Test for getProductSkus method.
     *
     * @return void
     */
    public function testGetProductSkus()
    {
        $productId = 1;
        $productSku = 'SKU01';
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')->with('entity_id', [$productId], 'in')->willReturnSelf();
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $searchResults = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductSearchResultsInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->productRepository->expects($this->once())
            ->method('getList')->with($searchCriteria)->willReturn($searchResults);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMock();
        $searchResults->expects($this->once())->method('getItems')->willReturn([$product]);
        $product->expects($this->once())->method('getId')->wilLReturn($productId);
        $product->expects($this->once())->method('getSku')->wilLReturn($productSku);
        $this->assertEquals([$productId => $productSku], $this->productSkuLocator->getProductSkus([$productId]));
    }
}
