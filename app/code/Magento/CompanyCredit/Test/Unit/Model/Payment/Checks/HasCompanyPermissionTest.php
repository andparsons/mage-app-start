<?php

namespace Magento\CompanyCredit\Test\Unit\Model\Payment\Checks;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Class HasCompanyPermissionTest.
 */
class HasCompanyPermissionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorization;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\CompanyCredit\Model\Payment\Checks\HasCompanyPermission
     */
    private $hasCompanyPermission;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->authorization = $this->getMockBuilder(\Magento\Company\Api\AuthorizationInterface::class)
            ->setMethods(['isAllowed'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->userContext = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->setMethods(['getUserType'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->hasCompanyPermission = $objectManager->getObject(
            \Magento\CompanyCredit\Model\Payment\Checks\HasCompanyPermission::class,
            [
                'authorization' => $this->authorization,
                'userContext' => $this->userContext
            ]
        );
    }

    /**
     * Test for method isApplicable.
     *
     * @return void
     */
    public function testIsApplicable()
    {
        $paymentMethod = $this->createMock(\Magento\Payment\Model\MethodInterface::class);
        $quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getCustomerId']);
        $quote->expects($this->once())->method('getCustomerId')->willReturn(1);
        $paymentMethod->expects($this->once())->method('getCode')->willReturn('paymentCode');
        $this->assertTrue($this->hasCompanyPermission->isApplicable($paymentMethod, $quote));
    }

    /**
     * Test for method isApplicable with non-authorized user.
     *
     * @return void
     */
    public function testIsApplicableWithNonAuthorizedUser()
    {
        $paymentMethod = $this->createMock(\Magento\Payment\Model\MethodInterface::class);
        $quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getCustomerId']);
        $quote->expects($this->once())->method('getCustomerId')->willReturn(null);
        $this->assertTrue($this->hasCompanyPermission->isApplicable($paymentMethod, $quote));
    }

    /**
     * Test for method isApplicable with Payment on Account method.
     *
     * @return void
     */
    public function testIsApplicableWithPaymentOnAccountMethod()
    {
        $paymentMethod = $this->createMock(\Magento\Payment\Model\MethodInterface::class);
        $paymentMethod->expects($this->once())->method('getCode')->willReturn(
            \Magento\CompanyCredit\Model\Payment\Checks\HasCompanyPermission::PAYMENT_ACCOUNT_METHOD_CODE
        );

        $quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getCustomerId']);
        $quote->expects($this->once())->method('getCustomerId')->willReturn(1);

        $userType = UserContextInterface::USER_TYPE_CUSTOMER;
        $this->userContext->expects($this->exactly(1))->method('getUserType')->willReturn($userType);

        $this->authorization->expects($this->once())
            ->method('isAllowed')->with('Magento_Sales::payment_account')->willReturn(false);
        $this->assertFalse($this->hasCompanyPermission->isApplicable($paymentMethod, $quote));
    }
}
