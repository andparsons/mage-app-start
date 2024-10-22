<?php

/**
 * Custom import CSV file field for shipping table rates
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\OfflineShipping\Test\Unit\Block\Adminhtml\Form\Field;

class ImportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\OfflineShipping\Block\Adminhtml\Form\Field\Import
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_formMock;

    protected function setUp()
    {
        $this->_formMock = $this->createPartialMock(
            \Magento\Framework\Data\Form::class,
            ['getFieldNameSuffix', 'addSuffixToName', 'getHtmlIdPrefix', 'getHtmlIdSuffix']
        );
        $testData = ['name' => 'test_name', 'html_id' => 'test_html_id'];
        $testHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_object = $testHelper->getObject(
            \Magento\OfflineShipping\Block\Adminhtml\Form\Field\Import::class,
            [
                'data' => $testData,
                '_escaper' => $testHelper->getObject(\Magento\Framework\Escaper::class)
            ]
        );
        $this->_object->setForm($this->_formMock);
    }

    public function testGetNameWhenFormFiledNameSuffixIsEmpty()
    {
        $this->_formMock->expects($this->once())->method('getFieldNameSuffix')->will($this->returnValue(false));
        $this->_formMock->expects($this->never())->method('addSuffixToName');
        $actual = $this->_object->getName();
        $this->assertEquals('test_name', $actual);
    }

    public function testGetNameWhenFormFiledNameSuffixIsNotEmpty()
    {
        $this->_formMock->expects($this->once())->method('getFieldNameSuffix')->will($this->returnValue(true));
        $this->_formMock->expects($this->once())->method('addSuffixToName')->will($this->returnValue('test_suffix'));
        $actual = $this->_object->getName();
        $this->assertEquals('test_suffix', $actual);
    }

    public function testGetElementHtml()
    {
        $this->_formMock->expects(
            $this->any()
        )->method(
            'getHtmlIdPrefix'
        )->will(
            $this->returnValue('test_name_prefix')
        );
        $this->_formMock->expects(
            $this->any()
        )->method(
            'getHtmlIdSuffix'
        )->will(
            $this->returnValue('test_name_suffix')
        );
        $testString = $this->_object->getElementHtml();
        $this->assertStringStartsWith(
            '<input id="time_condition" type="hidden" name="test_name" value="',
            $testString
        );
        $this->assertStringEndsWith(
            '<input id="test_name_prefixtest_html_idtest_name_suffix" ' .
            'name="test_name"  data-ui-id="form-element-test_name" value="" type="file"/>',
            $testString
        );
    }
}
