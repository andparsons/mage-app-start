<?php

namespace Magento\CompanyPayment\Test\Unit\Model\Payment\Checks;

/**
 * Class CanUseForCompanyTest.
 */
class CanUseForCompanyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var \Magento\CompanyPayment\Model\Payment\AvailabilityChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $availabilityChecker;

    /**
     * @var \Magento\CompanyPayment\Model\Payment\Checks\CanUseForCompany
     */
    private $canUseForCompany;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->companyManagement = $this->createMock(\Magento\Company\Api\CompanyManagementInterface::class);
        $this->availabilityChecker =
            $this->createMock(\Magento\CompanyPayment\Model\Payment\AvailabilityChecker::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->canUseForCompany = $objectManager->getObject(
            \Magento\CompanyPayment\Model\Payment\Checks\CanUseForCompany::class,
            [
                'companyManagement' => $this->companyManagement,
                'availabilityChecker' => $this->availabilityChecker,
            ]
        );
    }

    /**
     * Test isApplicable.
     *
     * @param \Magento\Payment\Model\MethodInterface $paymentMethod
     * @param int $customerId
     * @param \Magento\Company\Api\data\CompanyInterface|null $company
     * @param bool $result
     * @dataProvider dataProviderIsApplicable
     */
    public function testIsApplicable(
        \Magento\Payment\Model\MethodInterface $paymentMethod,
        $customerId,
        $company,
        $result
    ) {
        $quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getCustomerId']);
        $quote->expects($this->atLeastOnce())->method('getCustomerId')->willReturn($customerId);
        if ($customerId) {
            $this->companyManagement->expects($this->once())->method('getByCustomerId')->willReturn($company);
        }
        if ($company) {
            $this->availabilityChecker->expects($this->once())
                ->method('isAvailableForCompany')
                ->with('testCode', $company)
                ->willReturn($result);
        }

        $this->assertEquals($result, $this->canUseForCompany->isApplicable($paymentMethod, $quote));
    }

    /**
     * Dataprovider for isApplicable.
     *
     * @return array
     */
    public function dataProviderIsApplicable()
    {
        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        return [
            [$this->createMock(\Magento\Payment\Model\MethodInterface::class), null, null, true],
            [$this->createMock(\Magento\Payment\Model\MethodInterface::class), 1, null, true],
            [$this->getPaymentMethodMock(), 1, $company, true],
            [$this->getPaymentMethodMock(), 1, $company, false],
        ];
    }

    /**
     * Get payment method mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPaymentMethodMock()
    {
        $paymentMethod = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->setMethods(['getCode'])
            ->getMockForAbstractClass();
        $paymentMethod->expects($this->once())->method('getCode')->willReturn('testCode');

        return $paymentMethod;
    }
}
