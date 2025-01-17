<?php
namespace Magento\Framework\App\Test\Unit;

class ActionFlagTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    protected function setUp()
    {
        $this->_requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->_actionFlag = new \Magento\Framework\App\ActionFlag($this->_requestMock);
    }

    public function testSetIfActionNotExist()
    {
        $this->_requestMock->expects($this->once())->method('getActionName')->will($this->returnValue('action_name'));
        $this->_requestMock->expects($this->once())->method('getRouteName');
        $this->_requestMock->expects($this->once())->method('getControllerName');
        $this->_actionFlag->set('', 'flag', 'value');
    }

    public function testSetIfActionExist()
    {
        $this->_requestMock->expects($this->never())->method('getActionName');
        $this->_requestMock->expects($this->once())->method('getRouteName');
        $this->_requestMock->expects($this->once())->method('getControllerName');
        $this->_actionFlag->set('action', 'flag', 'value');
    }

    public function testGetIfFlagNotExist()
    {
        $this->_requestMock->expects($this->once())->method('getActionName')->will($this->returnValue('action_name'));
        $this->_requestMock->expects($this->once())->method('getRouteName');
        $this->_requestMock->expects($this->once())->method('getControllerName');
        $this->assertEquals([], $this->_actionFlag->get(''));
    }

    public function testGetIfFlagExist()
    {
        $this->_requestMock->expects($this->never())->method('getActionName');
        $this->_requestMock->expects(
            $this->exactly(3)
        )->method(
            'getRouteName'
        )->will(
            $this->returnValue('route')
        );
        $this->_requestMock->expects(
            $this->exactly(3)
        )->method(
            'getControllerName'
        )->will(
            $this->returnValue('controller')
        );
        $this->_actionFlag->set('action', 'flag', 'value');
        $this->assertEquals('value', $this->_actionFlag->get('action', 'flag'));
    }

    public function testGetIfFlagWithControllerKryNotExist()
    {
        $this->_requestMock->expects($this->never())->method('getActionName');
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getRouteName'
        )->will(
            $this->returnValue('route')
        );
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getControllerName'
        )->will(
            $this->returnValue('controller')
        );
        $this->assertEquals(false, $this->_actionFlag->get('action', 'flag'));
    }
}
