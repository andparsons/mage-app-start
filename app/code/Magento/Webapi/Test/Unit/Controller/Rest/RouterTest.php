<?php
namespace Magento\Webapi\Test\Unit\Controller\Rest;

class RouterTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Webapi\Controller\Rest\Router\Route */
    protected $_routeMock;

    /** @var \Magento\Framework\Webapi\Rest\Request */
    protected $_request;

    /** @var \Magento\Webapi\Model\Rest\Config */
    protected $_apiConfigMock;

    /** @var \Magento\Webapi\Controller\Rest\Router */
    protected $_router;

    protected function setUp()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_apiConfigMock = $this->getMockBuilder(
            \Magento\Webapi\Model\Rest\Config::class
        )->disableOriginalConstructor()->getMock();

        $this->_routeMock = $this->getMockBuilder(
            \Magento\Webapi\Controller\Rest\Router\Route::class
        )->disableOriginalConstructor()->setMethods(
            ['match']
        )->getMock();

        $areaListMock = $this->createMock(\Magento\Framework\App\AreaList::class);

        $areaListMock->expects($this->once())
            ->method('getFrontName')
            ->will($this->returnValue('rest'));

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_request = $objectManager->getObject(
            \Magento\Framework\Webapi\Rest\Request::class,
            [
                'areaList' => $areaListMock,
            ]
        );

        /** Initialize SUT. */
        $this->_router = $objectManager->getObject(
            \Magento\Webapi\Controller\Rest\Router::class,
            [
                'apiConfig' => $this->_apiConfigMock
            ]
        );
    }

    protected function tearDown()
    {
        unset($this->_routeMock);
        unset($this->_request);
        unset($this->_apiConfigMock);
        unset($this->_router);
        parent::tearDown();
    }

    public function testMatch()
    {
        $this->_apiConfigMock->expects(
            $this->once()
        )->method(
            'getRestRoutes'
        )->will(
            $this->returnValue([$this->_routeMock])
        );
        $this->_routeMock->expects(
            $this->once()
        )->method(
            'match'
        )->with(
            $this->_request
        )->will(
            $this->returnValue([])
        );

        $matchedRoute = $this->_router->match($this->_request);
        $this->assertEquals($this->_routeMock, $matchedRoute);
    }

    /**
     * @expectedException \Magento\Framework\Webapi\Exception
     */
    public function testNotMatch()
    {
        $this->_apiConfigMock->expects(
            $this->once()
        )->method(
            'getRestRoutes'
        )->will(
            $this->returnValue([$this->_routeMock])
        );
        $this->_routeMock->expects(
            $this->once()
        )->method(
            'match'
        )->with(
            $this->_request
        )->will(
            $this->returnValue(false)
        );

        $this->_router->match($this->_request);
    }
}
