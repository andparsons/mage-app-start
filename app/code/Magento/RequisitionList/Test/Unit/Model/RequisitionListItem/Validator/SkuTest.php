<?php

namespace Magento\RequisitionList\Test\Unit\Model\RequisitionListItem\Validator;

/**
 * Unit test for Sku validator.
 */
class SkuTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Model\OptionsManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionsManagement;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItemProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemProduct;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Validator\Sku
     */
    private $skuValidator;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->optionsManagement = $this->getMockBuilder(\Magento\RequisitionList\Model\OptionsManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListItemProduct = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItemProduct::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->skuValidator = $objectManager->getObject(
            \Magento\RequisitionList\Model\RequisitionListItem\Validator\Sku::class,
            [
                'optionsManagement' => $this->optionsManagement,
                'requisitionListItemProduct' => $this->requisitionListItemProduct,
            ]
        );
    }

    /**
     * Test for validate method.
     *
     * @return void
     */
    public function testValidate()
    {
        $buyRequestData = ['buy_request_data'];
        $itemOptions = ['option_ids' => '2,3'];
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('isProductAttached')
            ->willReturn(true);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['getStatus', 'isComposite', 'getTypeInstance', 'hasOptions', 'getOptions'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getStatus')
            ->willReturn(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $product->expects($this->once())->method('isComposite')->willReturn(true);
        $typeInstance = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->setMethods(['prepareForCartAdvanced'])
            ->getMockForAbstractClass();
        $product->expects($this->once())->method('getTypeInstance')->willReturn($typeInstance);
        $this->optionsManagement->expects($this->once())
            ->method('getInfoBuyRequest')->with($item)->willReturn($buyRequestData);
        $typeInstance->expects($this->once())->method('prepareForCartAdvanced')
            ->with($this->isInstanceOf(\Magento\Framework\DataObject::class), $product)->willReturn([]);
        $product->expects($this->once())->method('hasOptions')->willReturn(true);
        $item->expects($this->once())->method('getOptions')->willReturn($itemOptions);
        $option = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductCustomOptionInterface::class)
            ->disableOriginalConstructor()->getMock();
        $product->expects($this->once())->method('getOptions')->willReturn([$option, $option]);
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        $option->expects($this->exactly(2))->method('getOptionId')->willReturnOnConsecutiveCalls(2, 3);

        $this->assertEquals([], $this->skuValidator->validate($item));
    }

    /**
     * Test for validate method with disabled product.
     *
     * @return void
     */
    public function testValidateWithDisabledProduct()
    {
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('isProductAttached')
            ->willReturn(true);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['getStatus'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        $product->expects($this->once())->method('getStatus')
            ->willReturn(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('setIsProductAttached');

        $this->assertEquals(
            ['unavailable_sku' => __('The SKU was not found in the catalog.')],
            $this->skuValidator->validate($item)
        );
    }

    /**
     * Test for validate method without cart candidates.
     *
     * @return void
     */
    public function testValidateWithoutCartCandidates()
    {
        $buyRequestData = ['buy_request_data'];
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('isProductAttached')
            ->willReturn(true);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['getStatus', 'isComposite', 'getTypeInstance', 'hasOptions', 'getOptions'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $product->expects($this->once())->method('getStatus')
            ->willReturn(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $product->expects($this->once())->method('isComposite')->willReturn(true);
        $typeInstance = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->setMethods(['prepareForCartAdvanced'])
            ->getMockForAbstractClass();
        $product->expects($this->once())->method('getTypeInstance')->willReturn($typeInstance);
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        $this->optionsManagement->expects($this->once())
            ->method('getInfoBuyRequest')->with($item)->willReturn($buyRequestData);
        $typeInstance->expects($this->once())->method('prepareForCartAdvanced')
            ->with($this->isInstanceOf(\Magento\Framework\DataObject::class), $product)->willReturn('Error message');
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('setIsProductAttached');

        $this->assertEquals(
            ['options_updated' => __('Options were updated. Please review available configurations.')],
            $this->skuValidator->validate($item)
        );
    }

    /**
     * Test for validate method with changed options.
     *
     * @return void
     */
    public function testValidateWithChangedOptions()
    {
        $buyRequestData = ['buy_request_data'];
        $itemOptions = ['option_ids' => '2,3'];
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('isProductAttached')
            ->willReturn(true);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['getStatus', 'isComposite', 'getTypeInstance', 'hasOptions', 'getOptions'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getStatus')
            ->willReturn(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $product->expects($this->once())->method('isComposite')->willReturn(true);
        $typeInstance = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->setMethods(['prepareForCartAdvanced'])
            ->getMockForAbstractClass();
        $product->expects($this->once())->method('getTypeInstance')->willReturn($typeInstance);
        $this->optionsManagement->expects($this->once())
            ->method('getInfoBuyRequest')->with($item)->willReturn($buyRequestData);
        $typeInstance->expects($this->once())->method('prepareForCartAdvanced')
            ->with($this->isInstanceOf(\Magento\Framework\DataObject::class), $product)->willReturn([]);
        $product->expects($this->once())->method('hasOptions')->willReturn(true);
        $item->expects($this->once())->method('getOptions')->willReturn($itemOptions);
        $option = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductCustomOptionInterface::class)
            ->disableOriginalConstructor()->getMock();
        $product->expects($this->once())->method('getOptions')->willReturn([$option]);
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        $option->expects($this->once())->method('getOptionId')->willReturn(2);

        $this->assertEquals(
            ['options_updated' => __('Options were updated. Please review available configurations.')],
            $this->skuValidator->validate($item)
        );
    }

    /**
     * Test for validate() with empty buy request data.
     *
     * @return void
     */
    public function testValidateWithEmptyBuyRequestData()
    {
        $buyRequestData = [];
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('isProductAttached')
            ->willReturn(true);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['getStatus', 'isComposite', 'getTypeInstance'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getStatus')
            ->willReturn(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $product->expects($this->once())->method('isComposite')->willReturn(true);
        $typeInstance = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $product->expects($this->once())->method('getTypeInstance')->willReturn($typeInstance);
        $this->optionsManagement->expects($this->once())
            ->method('getInfoBuyRequest')->with($item)->willReturn($buyRequestData);
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);

        $this->assertEquals(
            ['options_updated' => __('Options were updated. Please review available configurations.')],
            $this->skuValidator->validate($item)
        );
    }
}
