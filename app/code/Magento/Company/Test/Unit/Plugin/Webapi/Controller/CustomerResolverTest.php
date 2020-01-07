<?php

namespace Magento\Company\Test\Unit\Plugin\Webapi\Controller;

/**
 * Class CustomerResolverTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Plugin\Webapi\Controller\CustomerResolver
     */
    private $customerResolver;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
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
        $this->customerResolver = $objectManagerHelper->getObject(
            \Magento\Company\Plugin\Webapi\Controller\CustomerResolver::class,
            [
                'userContext' => $this->userContext,
                'customerRepository' => $this->customerRepository
            ]
        );
    }

    /**
     * Test getCustomer method.
     *
     * @return void
     */
    public function testGetCustomer()
    {
        $userId = 1;
        $customer = $this
            ->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->userContext->expects($this->once())->method('getUserType')->willReturn(3);
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->customerRepository->expects($this->once())->method('getById')->willReturn($customer);
        $this->assertEquals($customer, $this->customerResolver->getCustomer());
    }

    /**
     * Test getCustomer method throws NoSuchEntityException exception.
     *
     * @return void
     */
    public function testGetCustomerWithException()
    {
        $userId = 1;
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->userContext->expects($this->once())->method('getUserType')->willReturn(3);
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->customerRepository->expects($this->once())->method('getById')->willThrowException($exception);
        $this->assertEquals(null, $this->customerResolver->getCustomer());
    }

    /**
     * Test getCustomer method with guest customer.
     *
     * @return void
     */
    public function testWithGuestCustomer()
    {
        $this->userContext->expects($this->once())->method('getUserType')->willReturn(4);
        $this->assertEquals(null, $this->customerResolver->getCustomer());
    }
}
