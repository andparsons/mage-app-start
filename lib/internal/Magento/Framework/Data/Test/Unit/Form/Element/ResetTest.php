<?php

/**
 * Tests for \Magento\Framework\Data\Form\Element\Reset
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

class ResetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Framework\Data\Form\Element\Reset
     */
    protected $_model;

    protected function setUp()
    {
        $factoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\Factory::class);
        $collectionFactoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\CollectionFactory::class);
        $escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
        $this->_model = new \Magento\Framework\Data\Form\Element\Reset(
            $factoryMock,
            $collectionFactoryMock,
            $escaperMock
        );
        $formMock = new \Magento\Framework\DataObject();
        $formMock->getHtmlIdPrefix('id_prefix');
        $formMock->getHtmlIdPrefix('id_suffix');
        $this->_model->setForm($formMock);
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Reset::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals('text', $this->_model->getType());
        $this->assertEquals('textfield', $this->_model->getExtType());
    }
}
