<?php
namespace Magento\Authorizenet\Test\Unit\Model\Response;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorizenet\Model\Response\Factory
     */
    protected $responseFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Authorizenet\Model\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->responseMock = $this->createMock(\Magento\Authorizenet\Model\Response::class);

        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Authorizenet\Model\Response::class, [])
            ->willReturn($this->responseMock);

        $this->responseFactory = $objectManager->getObject(
            \Magento\Authorizenet\Model\Response\Factory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $this->assertSame($this->responseMock, $this->responseFactory->create());
    }
}
