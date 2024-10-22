<?php
namespace Magento\GroupedProduct\Test\Unit\Block\Adminhtml\Product\Composite\Fieldset;

class GroupedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $pricingHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    protected function setUp()
    {
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->pricingHelperMock = $this->createMock(\Magento\Framework\Pricing\Helper\Data::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $customerMock = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerInterface::class
        )->disableOriginalConstructor()->getMock();
        $customerMock->expects($this->any())->method('getId')->will($this->returnValue(1));

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectHelper->getObject(
            \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::class,
            [
                'registry' => $this->registryMock,
                'storeManager' => $this->storeManagerMock,
                'pricingHelper' => $this->pricingHelperMock,
                'data' => ['product' => $this->productMock]
            ]
        );
    }

    /**
     * @covers \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getProduct
     */
    public function testGetProductPositive()
    {
        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);

        $this->productMock->expects($this->any())->method('getTypeInstance')->will($this->returnValue($instanceMock));

        $instanceMock->expects($this->once())->method('getStoreFilter')->will($this->returnValue($storeMock));

        $instanceMock->expects($this->never())->method('setStoreFilter');

        $this->assertEquals($this->productMock, $this->block->getProduct());
    }

    /**
     * @covers \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getProduct
     */
    public function testGetProductNegative()
    {
        $storeId = 2;
        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);

        $this->productMock->expects($this->any())->method('getTypeInstance')->will($this->returnValue($instanceMock));

        $instanceMock->expects(
            $this->once()
        )->method(
            'getStoreFilter'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue(null)
        );

        $this->productMock->expects($this->once())->method('getStoreId')->will($this->returnValue($storeId));

        $this->storeManagerMock->expects(
            $this->any()
        )->method(
            'getStore'
        )->with(
            $storeId
        )->will(
            $this->returnValue($storeMock)
        );

        $instanceMock->expects($this->once())->method('setStoreFilter')->with($storeMock, $this->productMock);

        $this->assertEquals($this->productMock, $this->block->getProduct());
    }

    /**
     * @covers \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getAssociatedProducts
     */
    public function testGetAssociatedProducts()
    {
        $storeId = 2;

        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);

        $this->productMock->expects($this->any())->method('getTypeInstance')->will($this->returnValue($instanceMock));

        $associatedProduct = clone $this->productMock;

        $associatedProduct->expects($this->once())->method('setStoreId')->with($storeId);

        $instanceMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue([$associatedProduct])
        );

        $this->productMock->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));

        $this->assertEquals([$associatedProduct], $this->block->getAssociatedProducts());
    }

    /**
     * @covers \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::setPreconfiguredValue
     */
    public function testSetPreconfiguredValue()
    {
        $storeId = 2;

        $objectMock = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getSuperGroup']);
        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);

        $objectMock->expects($this->once())->method('getSuperGroup')->will($this->returnValue([]));

        $this->productMock->expects(
            $this->once()
        )->method(
            'getPreconfiguredValues'
        )->will(
            $this->returnValue($objectMock)
        );

        $this->productMock->expects($this->any())->method('getTypeInstance')->will($this->returnValue($instanceMock));

        $associatedProduct = clone $this->productMock;

        $associatedProduct->expects($this->once())->method('setStoreId')->with($storeId);

        $instanceMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue([$associatedProduct])
        );

        $this->productMock->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));

        $this->assertEquals($this->block, $this->block->setPreconfiguredValue());
    }

    /**
     * @covers \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getCanShowProductPrice
     */
    public function testGetCanShowProductPrice()
    {
        $this->assertEquals(true, $this->block->getCanShowProductPrice($this->productMock));
    }

    /**
     * @covers \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getIsLastFieldset
     */
    public function testGetIsLastFieldsetPositive()
    {
        $this->block->setData('is_last_fieldset', true);

        $this->productMock->expects($this->never())->method('getOptions');

        $this->assertEquals(true, $this->block->getIsLastFieldset());
    }

    /**
     * @param array|bool $options
     * @param bool $expectedResult
     *
     * @covers \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getIsLastFieldset
     * @dataProvider getIsLastFieldsetDataProvider
     */
    public function testGetIsLastFieldsetNegative($options, $expectedResult)
    {
        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);

        $this->block->setData('is_last_fieldset', false);

        $this->productMock->expects($this->once())->method('getOptions')->will($this->returnValue($options));

        $this->productMock->expects($this->any())->method('getTypeInstance')->will($this->returnValue($instanceMock));

        $instanceMock->expects($this->once())->method('getStoreFilter')->will($this->returnValue(true));

        $this->assertEquals($expectedResult, $this->block->getIsLastFieldset());
    }

    /**
     * Data provider for testGetIsLastFieldsetNegative
     *
     * @return array
     */
    public function getIsLastFieldsetDataProvider()
    {
        return [
            'case1' => ['options' => false, 'expectedResult' => true],
            'case2' => ['options' => ['option'], 'expectedResult' => false]
        ];
    }

    /**
     * @covers \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getCurrencyPrice
     */
    public function testGetCurrencyPrice()
    {
        $storeId = 2;
        $price = 1.22;
        $expectedPrice = 1;

        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);

        $this->productMock->expects($this->any())->method('getTypeInstance')->will($this->returnValue($instanceMock));

        $instanceMock->expects($this->once())->method('getStoreFilter')->will($this->returnValue(true));

        $this->productMock->expects($this->once())->method('getStore')->will($this->returnValue($storeId));

        $this->pricingHelperMock->expects(
            $this->once()
        )->method(
            'currencyByStore'
        )->with(
            $price,
            $storeId,
            false
        )->will(
            $this->returnValue($expectedPrice)
        );

        $this->assertEquals($expectedPrice, $this->block->getCurrencyPrice($price));
    }
}
