<?php
namespace Magento\Framework\Controller\Test\Unit\Controller;

class NorouteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Controller\Noroute
     */
    protected $_controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_viewMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_statusMock;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->_viewMock = $this->createMock(\Magento\Framework\App\ViewInterface::class);
        $this->_statusMock =
            $this->createPartialMock(\Magento\Framework\DataObject::class, ['getLoaded', 'getForwarded']);
        $this->_controller = $helper->getObject(
            \Magento\Framework\Controller\Noroute\Index::class,
            ['request' => $this->_requestMock, 'view' => $this->_viewMock]
        );
    }

    public function testIndexActionWhenStatusNotLoaded()
    {
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            '__status__'
        )->will(
            $this->returnValue($this->_statusMock)
        );
        $this->_statusMock->expects($this->any())->method('getLoaded')->will($this->returnValue(false));
        $this->_viewMock->expects($this->once())->method('loadLayout')->with(['default', 'noroute']);
        $this->_viewMock->expects($this->once())->method('renderLayout');
        $this->_controller->execute();
    }

    public function testIndexActionWhenStatusLoaded()
    {
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            '__status__'
        )->will(
            $this->returnValue($this->_statusMock)
        );
        $this->_statusMock->expects($this->any())->method('getLoaded')->will($this->returnValue(true));
        $this->_statusMock->expects($this->any())->method('getForwarded')->will($this->returnValue(false));
        $this->_viewMock->expects($this->never())->method('loadLayout');
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'setActionName'
        )->will(
            $this->returnValue($this->_requestMock)
        );
        $this->_controller->execute();
    }

    public function testIndexActionWhenStatusNotInstanceofMagentoObject()
    {
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            '__status__'
        )->will(
            $this->returnValue('string')
        );
        $this->_controller->execute();
    }
}
