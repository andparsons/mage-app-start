<?php
namespace Magento\ConfigurableProduct\Test\Unit\Block\Product\Configurable;

class AttributeSelectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Block\Product\Configurable\AttributeSelector
     */
    protected $attributeSelector;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->attributeSelector = $helper->getObject(
            \Magento\ConfigurableProduct\Block\Product\Configurable\AttributeSelector::class,
            ['urlBuilder' => $this->urlBuilder]
        );
    }

    public function testGetAttributeSetCreationUrl()
    {
        $this->urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            '*/product_set/save'
        )->will(
            $this->returnValue('some_url')
        );
        $this->assertEquals('some_url', $this->attributeSelector->getAttributeSetCreationUrl());
    }

    public function testGetSuggestWidgetOptions()
    {
        $source = 'source_url';
        $this->urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            '*/product_attribute/suggestConfigurableAttributes'
        )->will(
            $this->returnValue($source)
        );
        $expected = ['source' => $source, 'minLength' => 0, 'className' => 'category-select', 'showAll' => true];
        $this->assertEquals($expected, $this->attributeSelector->getSuggestWidgetOptions());
    }
}
