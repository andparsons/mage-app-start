<?php
namespace Magento\Customer\Test\Unit\Model\Metadata;

use Magento\Customer\Model\Metadata\ElementFactory;

class ElementFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $_objectManager;

    /** @var \Magento\Customer\Model\Data\AttributeMetadata | \PHPUnit_Framework_MockObject_MockObject */
    private $_attributeMetadata;

    /** @var string */
    private $_entityTypeCode = 'customer_address';

    /** @var ElementFactory */
    private $_elementFactory;

    protected function setUp()
    {
        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_attributeMetadata = $this->createMock(\Magento\Customer\Model\Data\AttributeMetadata::class);
        $this->_elementFactory = new ElementFactory($this->_objectManager, new \Magento\Framework\Stdlib\StringUtils());
    }

    /** TODO fix when Validation is implemented MAGETWO-17341 */
    public function testAttributePostcodeDataModelClass()
    {
        $this->_attributeMetadata->expects(
            $this->once()
        )->method(
            'getDataModel'
        )->will(
            $this->returnValue(\Magento\Customer\Model\Attribute\Data\Postcode::class)
        );

        $dataModel = $this->createMock(\Magento\Customer\Model\Metadata\Form\Text::class);
        $this->_objectManager->expects($this->once())->method('create')->will($this->returnValue($dataModel));

        $actual = $this->_elementFactory->create($this->_attributeMetadata, '95131', $this->_entityTypeCode);
        $this->assertSame($dataModel, $actual);
    }

    public function testAttributeEmptyDataModelClass()
    {
        $this->_attributeMetadata->expects($this->once())->method('getDataModel')->will($this->returnValue(''));
        $this->_attributeMetadata->expects(
            $this->once()
        )->method(
            'getFrontendInput'
        )->will(
            $this->returnValue('text')
        );

        $dataModel = $this->createMock(\Magento\Customer\Model\Metadata\Form\Text::class);
        $params = [
            'entityTypeCode' => $this->_entityTypeCode,
            'value' => 'Some Text',
            'isAjax' => false,
            'attribute' => $this->_attributeMetadata,
        ];
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Magento\Customer\Model\Metadata\Form\Text::class,
            $params
        )->will(
            $this->returnValue($dataModel)
        );

        $actual = $this->_elementFactory->create($this->_attributeMetadata, 'Some Text', $this->_entityTypeCode);
        $this->assertSame($dataModel, $actual);
    }
}
