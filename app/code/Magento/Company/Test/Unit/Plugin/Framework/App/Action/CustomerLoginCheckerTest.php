<?php

namespace Magento\Company\Test\Unit\Plugin\Framework\App\Action;

use Magento\Authorization\Model\UserContextInterface;

class CustomerLoginCheckerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\Customer\PermissionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permission;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Plugin\Framework\App\Action\CustomerLoginChecker
     */
    private $customerLoginChecker;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->permission = $this
            ->getMockBuilder(\Magento\Company\Model\Customer\PermissionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isLoginAllowed'])
            ->getMockForAbstractClass();
        $this->userContext = $this
            ->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserType', 'getUserId'])
            ->getMockForAbstractClass();
        $this->customerRepository = $this
            ->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getById'])
            ->getMockForAbstractClass();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerLoginChecker = $objectManagerHelper->getObject(
            \Magento\Company\Plugin\Framework\App\Action\CustomerLoginChecker::class,
            [
                'userContext' => $this->userContext,
                'permission' => $this->permission,
                'customerRepository' => $this->customerRepository
            ]
        );
    }

    /**
     * Test isLoginAllowed method.
     *
     * @return void
     */
    public function testIsLoginAllowed()
    {
        $customer = $this
            ->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->userContext->expects($this->once())->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContext->expects($this->once())->method('getUserId')->willReturn(1);
        $this->customerRepository->expects($this->once())->method('getById')->willReturn($customer);
        $this->permission->expects($this->once())->method('isLoginAllowed')->willReturn(false);
        $this->assertTrue($this->customerLoginChecker->isLoginAllowed());
    }

    /**
     * Test exception in isLoginAllowed method.
     *
     * @return void
     */
    public function testIsLoginAllowedWithException()
    {
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->userContext->expects($this->once())->method('getUserType')->willReturn(3);
        $this->userContext->expects($this->once())->method('getUserId')->willReturn(1);
        $this->customerRepository->expects($this->once())->method('getById')->willThrowException($exception);
        $this->assertEquals(false, $this->customerLoginChecker->isLoginAllowed());
    }
}
