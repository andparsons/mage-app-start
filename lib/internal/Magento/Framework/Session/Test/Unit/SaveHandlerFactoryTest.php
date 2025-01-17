<?php
namespace Magento\Framework\Session\Test\Unit;

use \Magento\Framework\Session\SaveHandlerFactory;

class SaveHandlerFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate($handlers, $saveClass, $saveMethod)
    {
        $saveHandler = $this->createMock($saveClass);
        $objectManager = $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, ['create']);
        $objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo($saveClass),
            $this->equalTo([])
        )->will(
            $this->returnValue($saveHandler)
        );
        $model = new SaveHandlerFactory($objectManager, $handlers);
        $result = $model->create($saveMethod);
        $this->assertInstanceOf($saveClass, $result);
        $this->assertInstanceOf(\Magento\Framework\Session\SaveHandler\Native::class, $result);
        $this->assertInstanceOf('\SessionHandlerInterface', $result);
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [[[], \Magento\Framework\Session\SaveHandler\Native::class, 'files']];
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Magento\Framework\Session\SaveHandler\Native doesn't implement \SessionHandlerInterface
     */
    public function testCreateInvalid()
    {
        $invalidSaveHandler = new \Magento\Framework\DataObject();
        $objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->expects($this->once())
            ->method('create')
            ->willReturn($invalidSaveHandler);
        $model = new SaveHandlerFactory($objectManager, []);
        $model->create('files');
    }
}
