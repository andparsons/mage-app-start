<?php
namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Radio;

class RadioTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Radio
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_column;

    protected function setUp()
    {
        $context = $this->createMock(\Magento\Backend\Block\Context::class);
        $this->_converter = $this->createPartialMock(
            \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter::class,
            ['toFlatArray']
        );
        $this->_column = $this->createPartialMock(
            \Magento\Backend\Block\Widget\Grid\Column::class,
            ['getValues', 'getIndex', 'getHtmlName']
        );
        $this->_object = new \Magento\Backend\Block\Widget\Grid\Column\Renderer\Radio($context, $this->_converter);
        $this->_object->setColumn($this->_column);
    }

    /**
     * @param array $rowData
     * @param string $expectedResult
     * @dataProvider renderDataProvider
     */
    public function testRender(array $rowData, $expectedResult)
    {
        $selectedTreeArray = [['value' => 1, 'label' => 'One']];
        $selectedFlatArray = [1 => 'One'];
        $this->_column->expects($this->once())->method('getValues')->will($this->returnValue($selectedTreeArray));
        $this->_column->expects($this->once())->method('getIndex')->will($this->returnValue('label'));
        $this->_column->expects($this->once())->method('getHtmlName')->will($this->returnValue('test[]'));
        $this->_converter->expects(
            $this->once()
        )->method(
            'toFlatArray'
        )->with(
            $selectedTreeArray
        )->will(
            $this->returnValue($selectedFlatArray)
        );
        $this->assertEquals($expectedResult, $this->_object->render(new \Magento\Framework\DataObject($rowData)));
    }

    /**
     * @return array
     */
    public function renderDataProvider()
    {
        return [
            'checked' => [
                ['id' => 1, 'label' => 'One'],
                '<input type="radio" name="test[]" value="1" class="radio" checked="checked"/>',
            ],
            'not checked' => [
                ['id' => 2, 'label' => 'Two'],
                '<input type="radio" name="test[]" value="2" class="radio"/>',
            ]
        ];
    }
}
