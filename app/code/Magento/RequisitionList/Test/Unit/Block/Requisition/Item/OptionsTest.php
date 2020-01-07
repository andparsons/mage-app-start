<?php

namespace Magento\RequisitionList\Test\Unit\Block\Requisition\Item;

/**
 * Unit test for Options block.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helperPool;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItemProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemProduct;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItemOptionsLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemOptionsLocator;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\RequisitionList\Block\Requisition\Item\Options
     */
    private $options;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->helperPool = $this->getMockBuilder(\Magento\Catalog\Helper\Product\ConfigurationPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListItemProduct = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItemProduct::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListItemOptionsLocator = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItemOptionsLocator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem = $this
            ->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ignoreTypes = ['giftcard'];

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->options = $objectManager->getObject(
            \Magento\RequisitionList\Block\Requisition\Item\Options::class,
            [
                'helperPool' => $this->helperPool,
                'requisitionListItemProduct' => $this->requisitionListItemProduct,
                'requisitionListItemOptionsLocator' => $this->requisitionListItemOptionsLocator,
                '_filesystem' => $this->filesystem,
                'ignoreTypes' => $ignoreTypes,
                'data' => []
            ]
        );
    }

    /**
     * Test for addOptionsRenderCfg method.
     *
     * @return void
     */
    public function testAddOptionsRenderCfg()
    {
        $this->assertInstanceOf(
            \Magento\RequisitionList\Block\Requisition\Item\Options::class,
            $this->options->addOptionsRenderCfg('simple', 'helper_name')
        );
    }

    /**
     * Test for getOptionsRenderCfg method.
     *
     * @return void
     */
    public function testGetOptionsRenderCfg()
    {
        $this->options->addOptionsRenderCfg('simple', 'helper_name');
        $this->assertEquals(
            [
                'helper' => 'helper_name',
                'template' => null
            ],
            $this->options->getOptionsRenderCfg('simple')
        );
        $this->assertEquals(
            [
                'helper' => \Magento\Catalog\Helper\Product\Configuration::class,
                'template' => 'Magento_RequisitionList::requisition/view/items/options_list.phtml'
            ],
            $this->options->getOptionsRenderCfg('product_type')
        );
    }

    /**
     * Test for getConfiguredOptions method.
     *
     * @param string $productType
     * @param bool $hasOptions
     * @param array $options
     * @param int $optionsCalls
     * @param int $helperCalls
     * @return void
     * @dataProvider getConfiguredOptionsDataProvider
     */
    public function testGetConfiguredOptions($productType, $hasOptions, array $options, $optionsCalls, $helperCalls)
    {
        $typeInstance = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()->getMock();
        $typeInstance->expects($this->exactly($optionsCalls))->method('hasOptions')->willReturn($hasOptions);
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()->getMock();
        $product->expects($this->atLeastOnce())->method('getTypeId')->willReturn($productType);
        $product->expects($this->exactly($optionsCalls))->method('getTypeInstance')->willReturn($typeInstance);
        $item = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem::class)
            ->disableOriginalConstructor()->getMock();
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        $this->options->setData('item', $item);
        $helper = $this->getMockBuilder(\Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface::class)
            ->disableOriginalConstructor()->getMock();
        $helper->expects($this->exactly($helperCalls))->method('getOptions')->willReturn($options);
        $this->helperPool->expects($this->exactly($helperCalls))->method('get')->willReturn($helper);
        $this->options->setItem($item);
        $requisitionListItemOptions = $this
            ->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListItemOptionsLocator->expects($this->exactly($helperCalls))->method('getOptions')
            ->willReturn($requisitionListItemOptions);

        $this->assertEquals($options, $this->options->getConfiguredOptions());
    }

    /**
     * Test for getConfiguredOptions method with NoSuchEntityException.
     *
     * @return void
     */
    public function testGetConfiguredOptionsWithNoSuchEntityException()
    {
        $exception = new \Magento\Framework\Exception\NoSuchEntityException(__('Exception'));
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')
            ->willThrowException($exception);
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->options->setItem($item);
        $this->assertEquals([], $this->options->getConfiguredOptions());
    }

    /**
     * Test for getTemplate method.
     *
     * @param string $template
     * @param bool $noProduct
     * @param string $productType
     * @param string $templateName
     * @param int $typeCalls
     * @return void
     * @dataProvider getTemplateDataProvider
     */
    public function testGetTemplate($template, $noProduct, $productType, $templateName, $typeCalls)
    {
        $this->options->addOptionsRenderCfg('simple', 'helper_name', 'template_name');
        $this->options->setData('template', $template);
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->exactly($typeCalls))->method('getTypeId')->willReturn($productType);
        $item = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('isProductAttached')
            ->willReturn($noProduct);
        $this->requisitionListItemProduct->expects($this->exactly($typeCalls))->method('getProduct')
            ->willReturn($product);
        $this->options->setData('item', $item);
        $this->options->setItem($item);
        $this->assertEquals($templateName, $this->options->getTemplate());
    }

    /**
     * Test for getTemplate method without item.
     *
     * @return void
     */
    public function testGetTemplateWithoutItem()
    {
        $this->assertEquals('', $this->options->getTemplate());
    }

    /**
     * Test for getTemplate method with NoSuchEntityException.
     *
     * @return void
     */
    public function testGetTemplateWithNoSuchEntityException()
    {
        $item = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->options->setItem($item);
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('isProductAttached')
            ->willReturn(true);
        $exception = new \Magento\Framework\Exception\NoSuchEntityException(__('Exception'));
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')
            ->willThrowException($exception);

        $this->assertEquals(
            'Magento_RequisitionList::requisition/view/items/options_list.phtml',
            $this->options->getTemplate()
        );
    }

    /**
     * Test for getTemplate method with simple return template name.
     *
     * @return void
     */
    public function testGetTemplateWithSimpleReturn()
    {
        $templateName = 'Magento_RequisitionList::requisition/view/items/options_list.phtml';
        $this->options->setTemplate($templateName);
        $this->assertEquals($templateName, $this->options->getTemplate());
    }

    /**
     * Test for toHtml method.
     *
     * @return void
     */
    public function testToHtml()
    {
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->options->setItem($item);
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('isProductAttached')
            ->willReturn(true);
        $exception = new \Magento\Framework\Exception\NoSuchEntityException(__('Exception'));
        $this->requisitionListItemProduct->expects($this->atLeastOnce())->method('getProduct')
            ->willThrowException($exception);
        $directory = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem->expects($this->once())->method('getDirectoryRead')->willReturn($directory);
        $this->assertEquals('', $this->options->toHtml());
        $this->assertEquals([], $this->options->getOptionList());
    }

    /**
     * Data provider for testGetConfiguredOptions.
     *
     * @return array
     */
    public function getConfiguredOptionsDataProvider()
    {
        return [
            ['giftcard', false, [], 0, 0],
            [\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE, false, [], 1, 0],
            [\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE, true, ['option'], 1, 1],
            [\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE, true, ['option'], 1, 1]
        ];
    }

    /**
     * Data provider for testGetTemplate.
     *
     * @return array
     */
    public function getTemplateDataProvider()
    {
        return [
            ['template_name', true, 'simple', 'template_name', 1],
            ['', true, 'simple', 'template_name', 1],
            ['', false, '', 'Magento_RequisitionList::requisition/view/items/options_list.phtml', 0]
        ];
    }
}
