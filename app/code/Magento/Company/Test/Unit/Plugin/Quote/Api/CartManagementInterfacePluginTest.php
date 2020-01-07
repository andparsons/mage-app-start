<?php

namespace Magento\Company\Test\Unit\Plugin\Quote\Api;

/**
 * Class CartManagementInterfacePluginTest.
 */
class CartManagementInterfacePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Model\Customer\PermissionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permission;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Company\Plugin\Quote\Api\CartManagementInterfacePlugin
     */
    private $plugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->userContext = $this->createMock(
            \Magento\Authorization\Model\UserContextInterface::class
        );
        $this->customerRepository = $this
            ->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getById'])
            ->getMockForAbstractClass();
        $this->permission = $this
            ->getMockBuilder(\Magento\Company\Model\Customer\PermissionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isCheckoutAllowed'])
            ->getMockForAbstractClass();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManagerHelper->getObject(
            \Magento\Company\Plugin\Quote\Api\CartManagementInterfacePlugin::class,
            [
                'customerRepository' => $this->customerRepository,
                'userContext' => $this->userContext,
                'permission' => $this->permission
            ]
        );
    }

    /**
     * Test beforePlaceOrder method.
     *
     * @return void
     */
    public function testBeforePlaceOrder()
    {
        $userId = 1;
        $cartId = 5;
        $customer = $this
            ->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $subject = $this
            ->getMockBuilder(\Magento\Quote\Api\CartManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->userContext->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);
        $this->customerRepository->expects($this->once())->method('getById')->willReturn($customer);
        $this->permission->expects($this->once())->method('isCheckoutAllowed')->with($customer)->willReturn(true);
        $this->assertEquals([$cartId, null], $this->plugin->beforePlaceOrder($subject, $cartId));
    }

    /**
     * Test beforePlaceOrder method throws LocalizedException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage This customer company account is blocked and customer cannot place orders.
     */
    public function testBeforePlaceOrderWithException()
    {
        $userId = 1;
        $cartId = 5;
        $customer = $this
            ->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $subject = $this
            ->getMockBuilder(\Magento\Quote\Api\CartManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->userContext->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);
        $this->customerRepository->expects($this->once())->method('getById')->willReturn($customer);
        $this->permission->expects($this->once())->method('isCheckoutAllowed')->with($customer)->willReturn(false);
        $this->assertEquals([$cartId, null], $this->plugin->beforePlaceOrder($subject, $cartId));
    }
}
