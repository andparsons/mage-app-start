<?php

namespace Magento\RequisitionList\Test\Unit\Model;

use Magento\RequisitionList\Model\RequisitionListItem\ProductExtractor;

/**
 * Unit test for RequisitionListItemProduct model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RequisitionListItemProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\RequisitionList\Model\OptionsManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionsManagement;

    /**
     * @var ProductExtractor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productExtractor;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItemProduct
     */
    private $requisitionListItemProduct;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'getList'])
            ->getMockForAbstractClass();
        $this->optionsManagement = $this->getMockBuilder(\Magento\RequisitionList\Model\OptionsManagement::class)
            ->disableOriginalConstructor()->getMock();
        $this->productExtractor = $this->getMockBuilder(ProductExtractor::class)
            ->disableOriginalConstructor()->setMethods(['extract'])->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->requisitionListItemProduct = $objectManager->getObject(
            \Magento\RequisitionList\Model\RequisitionListItemProduct::class,
            [
                'productRepository' => $this->productRepository,
                'optionsManagement' => $this->optionsManagement,
                'productExtractor' => $this->productExtractor
            ]
        );
    }

    /**
     * Test for setProduct method.
     *
     * @return void
     */
    public function testSetProduct()
    {
        $itemId = 1;
        $itemSku = 'SKU-01';
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $item->expects($this->once())->method('getId')->willReturn($itemId);
        $item->expects($this->once())->method('getSku')->willReturn($itemSku);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->requisitionListItemProduct->setProduct($item, $product);
    }

    /**
     * Test for getProduct method.
     *
     * @return void
     */
    public function testGetProduct()
    {
        $itemId = 1;
        $itemSku = 'SKU-01';
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $item->expects($this->once())->method('getId')->willReturn($itemId);
        $item->expects($this->atLeastOnce())->method('getSku')->willReturn($itemSku);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['setCustomOptions'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->productRepository->expects($this->once())
            ->method('get')->with($itemSku, false, null, true)->willReturn($product);
        $option = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\Option::class)
            ->disableOriginalConstructor()->getMock();
        $this->optionsManagement->expects($this->once())
            ->method('getOptions')->with($item, $product)->willReturn([$option]);
        $product->expects($this->once())->method('setCustomOptions')->with([$option])->willReturnSelf();
        $this->assertEquals($product, $this->requisitionListItemProduct->getProduct($item));
    }

    /**
     * Test for getProduct method with preset product.
     *
     * @return void
     */
    public function testGetProductWithPresetProduct()
    {
        $itemId = 1;
        $itemSku = 'SKU-01';
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $item->expects($this->atLeastOnce())->method('getId')->willReturn($itemId);
        $item->expects($this->atLeastOnce())->method('getSku')->willReturn($itemSku);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['setCustomOptions'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->productRepository->expects($this->never())->method('get');
        $option = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\Option::class)
            ->disableOriginalConstructor()->getMock();
        $this->optionsManagement->expects($this->once())
            ->method('getOptions')->with($item, $product)->willReturn([$option]);
        $product->expects($this->once())->method('setCustomOptions')->with([$option])->willReturnSelf();
        $this->requisitionListItemProduct->setProduct($item, $product);
        $this->assertEquals($product, $this->requisitionListItemProduct->getProduct($item));
    }

    /**
     * Test for setIsProductAttached method.
     *
     * @return void
     */
    public function testSetIsProductAttached()
    {
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $item->expects($this->once())->method('getId')->willReturn(null);
        $this->requisitionListItemProduct->setIsProductAttached($item, true);
    }

    /**
     * Test for isProductAttached method.
     *
     * @return void
     */
    public function testIsProductAttached()
    {
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $item->expects($this->once())->method('getId')->willReturn(null);
        $this->assertFalse($this->requisitionListItemProduct->isProductAttached($item));
    }

    /**
     * Test for isProductAttached method with preset value.
     *
     * @return void
     */
    public function testIsProductAttachedWithPresetValue()
    {
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $item->expects($this->atLeastOnce())->method('getId')->willReturn(null);
        $this->requisitionListItemProduct->setIsProductAttached($item, true);
        $this->assertTrue($this->requisitionListItemProduct->isProductAttached($item));
    }

    /**
     * Test for extract method.
     *
     * @return void
     */
    public function testExtract()
    {
        $productSku = 'SKU-01';
        $websiteId = 1;

        $requisitionListItem = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem::class)
            ->disableOriginalConstructor()->getMock();
        $requisitionListItem->expects($this->once())->method('getSku')->willReturn($productSku);

        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->productExtractor->expects($this->atLeastOnce())->method('extract')
            ->with([$productSku], $websiteId, true)->willReturn([$productSku => $product]);

        $this->assertEquals(
            [$productSku => $product],
            $this->requisitionListItemProduct->extract([$requisitionListItem], $websiteId)
        );
    }
}
