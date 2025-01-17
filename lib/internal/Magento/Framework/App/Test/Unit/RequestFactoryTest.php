<?php
namespace Magento\Framework\App\Test\Unit;

use \Magento\Framework\App\RequestFactory;

class RequestFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RequestFactory
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->model = new RequestFactory($this->objectManagerMock);
    }

    /**
     * @covers \Magento\Framework\App\RequestFactory::__construct
     * @covers \Magento\Framework\App\RequestFactory::create
     */
    public function testCreate()
    {
        $arguments = ['some_key' => 'same_value'];

        $appRequest = $this->createMock(\Magento\Framework\App\RequestInterface::class);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\App\RequestInterface::class, $arguments)
            ->will($this->returnValue($appRequest));

        $this->assertEquals($appRequest, $this->model->create($arguments));
    }
}
