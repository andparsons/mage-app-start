<?php

namespace Magento\Company\Test\Unit\Controller\Customer;

/**
 * Class ManageTest.
 */
class ManageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\StatusServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleConfig;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorization;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Company\Controller\Customer\Manage
     */
    private $manage;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->moduleConfig = $this->createMock(\Magento\Company\Api\StatusServiceInterface::class);
        $this->userContext = $this->createMock(
            \Magento\Authorization\Model\UserContextInterface::class
        );
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->authorization = $this->createMock(\Magento\Company\Api\AuthorizationInterface::class);
        $this->resultFactory = $this->createPartialMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create']
        );
        $this->request = $this->createMock(
            \Magento\Framework\App\RequestInterface::class
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->manage = $objectManager->getObject(
            \Magento\Company\Controller\Customer\Manage::class,
            [
                'request' => $this->request,
                'moduleConfig' => $this->moduleConfig,
                'userContext' => $this->userContext,
                'logger' => $this->logger,
                'authorization' => $this->authorization,
                'resultFactory' => $this->resultFactory
            ]
        );
    }

    /**
     * Test execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $this->request->expects($this->once())->method('getParam')->with('customer_id')->willReturn(1);
        $result = $this->createMock(\Magento\Framework\Controller\Result\Forward::class);
        $result->expects($this->once())->method('forward')->willReturnSelf();
        $this->resultFactory->expects($this->once())->method('create')->willReturn($result);

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Forward::class, $this->manage->execute());
    }
}
