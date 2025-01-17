<?php

namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class ShareTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Wishlist\Controller\Index\Share
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactoryMock;

    protected function setUp()
    {
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->contextMock = $this->createMock(\Magento\Framework\App\Action\Context::class);
        $this->resultFactoryMock = $this->createMock(\Magento\Framework\Controller\ResultFactory::class);

        $this->contextMock->expects($this->any())->method('getResultFactory')->willReturn($this->resultFactoryMock);

        $this->model = new \Magento\Wishlist\Controller\Index\Share(
            $this->contextMock,
            $this->customerSessionMock
        );
    }

    public function testExecute()
    {
        $resultMock = $this->createMock(\Magento\Framework\Controller\ResultInterface::class);

        $this->customerSessionMock->expects($this->once())->method('authenticate')
            ->willReturn(true);
        $this->resultFactoryMock->expects($this->once())->method('create')->with(ResultFactory::TYPE_PAGE)
            ->willReturn($resultMock);

        $this->assertEquals($resultMock, $this->model->execute());
    }

    public function testExecuteAuthenticationFail()
    {
        $this->customerSessionMock->expects($this->once())->method('authenticate')
            ->willReturn(false);

        $this->assertEmpty($this->model->execute());
    }
}
