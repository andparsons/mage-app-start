<?php

/**
 * Tests for \Magento\Framework\Data\Form\Element\Imagefile
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

class ImagefileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Framework\Data\Form\Element\Imagefile
     */
    protected $_imagefile;

    protected function setUp()
    {
        $factoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\Factory::class);
        $collectionFactoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\CollectionFactory::class);
        $escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
        $this->_imagefile = new \Magento\Framework\Data\Form\Element\Imagefile(
            $factoryMock,
            $collectionFactoryMock,
            $escaperMock
        );
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Imagefile::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals('file', $this->_imagefile->getType());
        $this->assertEquals('imagefile', $this->_imagefile->getExtType());
        $this->assertFalse($this->_imagefile->getAutosubmit());
        $this->assertFalse($this->_imagefile->getData('autoSubmit'));
    }
}
