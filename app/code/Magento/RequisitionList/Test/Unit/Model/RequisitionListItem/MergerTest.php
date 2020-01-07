<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Unit\Model\RequisitionListItem;

/**
 * Unit test for items merger.
 */
class MergerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItemProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemProduct;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Merger
     */
    private $merger;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->requisitionListItemProduct = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItemProduct::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->merger = $objectManager->getObject(
            \Magento\RequisitionList\Model\RequisitionListItem\Merger::class,
            [
                'requisitionListItemProduct' => $this->requisitionListItemProduct
            ]
        );
    }

    /**
     * Test for merge method.
     *
     * @return void
     */
    public function testMerge()
    {
        $productId = 1;
        $sku = 'SKU01';
        $requisitionListItems = [
            $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
                ->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
                ->disableOriginalConstructor()->getMock(),
        ];
        $requisitionListItems[0]->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $requisitionListItems[1]->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['getId', 'getCustomOptions'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        $product->expects($this->atLeastOnce())->method('getId')->willReturn($productId);
        $option = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductOptionInterface::class)
            ->setMethods(['getCode', 'getValue'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getCustomOptions')
            ->willReturn(['info_buyRequest' => $option, 'custom_option' => $option]);
        $option->expects($this->atLeastOnce())->method('getCode')
            ->willReturnOnConsecutiveCalls('info_buyRequest', 'custom_option', 'info_buyRequest', 'custom_option');
        $option->expects($this->atLeastOnce())->method('getValue')->willReturn('option_value');
        $requisitionListItems[0]->expects($this->atLeastOnce())->method('getQty')->willReturn(1);
        $requisitionListItems[1]->expects($this->atLeastOnce())->method('getQty')->willReturn(2);
        $requisitionListItems[0]->expects($this->atLeastOnce())->method('setQty')->with(3)->willReturnSelf();
        $this->assertEquals(
            [$requisitionListItems[0]],
            $this->merger->merge([$requisitionListItems[0], $requisitionListItems[1]])
        );
    }

    /**
     * Test for merge method with different product skus.
     *
     * @return void
     */
    public function testMergeWithDifferentProductSkus()
    {
        $skus = ['SKU01', 'SKU02'];
        $requisitionListItems = [
            $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
                ->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
                ->disableOriginalConstructor()->getMock(),
        ];
        $requisitionListItems[0]->expects($this->atLeastOnce())->method('getSku')->willReturn($skus[0]);
        $requisitionListItems[1]->expects($this->atLeastOnce())->method('getSku')->willReturn($skus[1]);
        $this->assertEquals(
            [$requisitionListItems[0], $requisitionListItems[1]],
            $this->merger->merge([$requisitionListItems[0], $requisitionListItems[1]])
        );
    }

    /**
     * Test for merge method with non-existing product.
     *
     * @return void
     */
    public function testMergeWithNonExistingProduct()
    {
        $sku = 'SKU01';
        $requisitionListItems = [
            $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
                ->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
                ->disableOriginalConstructor()->getMock(),
        ];
        $requisitionListItems[0]->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $requisitionListItems[1]->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->assertEquals(
            [$requisitionListItems[0], $requisitionListItems[1]],
            $this->merger->merge([$requisitionListItems[0], $requisitionListItems[1]])
        );
    }

    /**
     * Test for merge method with different product ids.
     *
     * @return void
     */
    public function testMergeWithDifferentProductIds()
    {
        $productIds = [1, 2];
        $sku = 'SKU01';
        $requisitionListItems = [
            $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
                ->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
                ->disableOriginalConstructor()->getMock(),
        ];
        $requisitionListItems[0]->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $requisitionListItems[1]->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['getId', 'getCustomOptions'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        $product->expects($this->atLeastOnce())
            ->method('getId')->willReturnOnConsecutiveCalls($productIds[0], $productIds[1]);
        $this->assertEquals(
            [$requisitionListItems[0], $requisitionListItems[1]],
            $this->merger->merge([$requisitionListItems[0], $requisitionListItems[1]])
        );
    }

    /**
     * Test for merge method with different product options.
     *
     * @return void
     */
    public function testMergeWithDifferentProductOptions()
    {
        $productId = 1;
        $sku = 'SKU01';
        $requisitionListItems = [
            $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
                ->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
                ->disableOriginalConstructor()->getMock(),
        ];
        $requisitionListItems[0]->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $requisitionListItems[1]->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['getId', 'getCustomOptions'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        $product->expects($this->atLeastOnce())->method('getId')->willReturn($productId);
        $option = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductOptionInterface::class)
            ->setMethods(['getCode', 'getValue'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getCustomOptions')
            ->willReturn(['info_buyRequest' => $option, 'custom_option' => $option]);
        $option->expects($this->atLeastOnce())->method('getCode')
            ->willReturnOnConsecutiveCalls('info_buyRequest', 'custom_option', 'info_buyRequest');
        $option->expects($this->atLeastOnce())->method('getValue')->willReturn('option_value');
        $this->assertEquals(
            [$requisitionListItems[0], $requisitionListItems[1]],
            $this->merger->merge([$requisitionListItems[0], $requisitionListItems[1]])
        );
    }

    /**
     * Test for mergeItem method.
     *
     * @return void
     */
    public function testMergeItem()
    {
        $productId = 1;
        $sku = 'SKU01';
        $requisitionListItems = [
            $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
                ->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
                ->disableOriginalConstructor()->getMock(),
        ];
        $requisitionListItems[0]->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $requisitionListItems[1]->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['getId', 'getCustomOptions'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        $product->expects($this->atLeastOnce())->method('getId')->willReturn($productId);
        $option = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductOptionInterface::class)
            ->setMethods(['getCode', 'getValue'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getCustomOptions')
            ->willReturn(['info_buyRequest' => $option, 'custom_option' => $option]);
        $option->expects($this->atLeastOnce())->method('getCode')
            ->willReturnOnConsecutiveCalls('info_buyRequest', 'custom_option', 'info_buyRequest', 'custom_option');
        $option->expects($this->atLeastOnce())->method('getValue')->willReturn('option_value');
        $requisitionListItems[0]->expects($this->atLeastOnce())->method('getQty')->willReturn(1);
        $requisitionListItems[1]->expects($this->atLeastOnce())->method('getQty')->willReturn(2);
        $requisitionListItems[1]->expects($this->atLeastOnce())->method('setQty')->with(3)->willReturnSelf();
        $this->assertEquals(
            [$requisitionListItems[0]],
            $this->merger->mergeItem([$requisitionListItems[0]], $requisitionListItems[1])
        );
    }

    /**
     * Test for mergeItem method with different product sku.
     *
     * @return void
     */
    public function testMergeItemWithDifferentProductSku()
    {
        $skus = ['SKU01', 'SKU02'];
        $requisitionListItems = [
            $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
                ->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
                ->disableOriginalConstructor()->getMock(),
        ];
        $requisitionListItems[0]->expects($this->atLeastOnce())->method('getSku')->willReturn($skus[0]);
        $requisitionListItems[1]->expects($this->atLeastOnce())->method('getSku')->willReturn($skus[1]);
        $this->assertEquals(
            [$requisitionListItems[0], $requisitionListItems[1]],
            $this->merger->mergeItem([$requisitionListItems[0]], $requisitionListItems[1])
        );
    }
}
