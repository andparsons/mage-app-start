<?php

namespace Magento\CompanyPayment\Test\Unit\Model\Company\SaveHandler;

/**
 * Class AvailablePaymentMethodsTest.
 */
class AvailablePaymentMethodsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyPayment\Model\ResourceModel\CompanyPaymentMethod|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyPaymentMethodResource;

    /**
     * @var \Magento\CompanyPayment\Model\CompanyPaymentMethodFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyPaymentMethodFactory;

    /**
     * @var \Magento\CompanyPayment\Model\Company\SaveHandler\AvailablePaymentMethods
     */
    private $availablePaymentMethods;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyPaymentMethodResource = $this->createMock(
            \Magento\CompanyPayment\Model\ResourceModel\CompanyPaymentMethod::class
        );
        $this->companyPaymentMethodFactory = $this->createPartialMock(
            \Magento\CompanyPayment\Model\CompanyPaymentMethodFactory::class,
            ['create']
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->availablePaymentMethods = $objectManager->getObject(
            \Magento\CompanyPayment\Model\Company\SaveHandler\AvailablePaymentMethods::class,
            [
                'companyPaymentMethodResource' => $this->companyPaymentMethodResource,
                'companyPaymentMethodFactory' => $this->companyPaymentMethodFactory,
            ]
        );
    }

    /**
     * Test for method execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $companyId = 1;
        $applicablePayment = ['payment'];
        $availablePayments = [$applicablePayment[0], 'payment2'];
        $useConfigSettings = true;
        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $company->expects($this->exactly(2))->method('getId')->willReturn($companyId);
        $initialCompany = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $extensionAttributes = $this->getMockForAbstractClass(
            \Magento\Company\Api\Data\CompanyExtensionInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'getApplicablePaymentMethod',
                'getAvailablePaymentMethods',
                'getUseConfigSettings',
                'setApplicablePaymentMethod',
                'setAvailablePaymentMethods',
                'setUseConfigSettings'
            ]
        );
        $company->expects($this->exactly(2))->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $initialExtensionAttributes = $this->getMockForAbstractClass(
            \Magento\Company\Api\Data\CompanyExtensionInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getApplicablePaymentMethod', 'getAvailablePaymentMethods', 'getUseConfigSettings']
        );
        $initialCompany->expects($this->once())
            ->method('getExtensionAttributes')->willReturn($initialExtensionAttributes);
        $extensionAttributes->expects($this->exactly(2))
            ->method('getApplicablePaymentMethod')->willReturn($applicablePayment);
        $extensionAttributes->method('getAvailablePaymentMethods')->willReturn($availablePayments);
        $extensionAttributes->expects($this->once())->method('getUseConfigSettings')->willReturn($useConfigSettings);
        $initialExtensionAttributes->expects($this->once())
            ->method('getApplicablePaymentMethod')->willReturn($availablePayments[1]);
        $paymentSettings = $this->createPartialMock(
            \Magento\CompanyPayment\Model\CompanyPaymentMethod::class,
            [
                'load',
                'getId',
                'setCompanyId',
                'setApplicablePaymentMethod',
                'setAvailablePaymentMethods',
                'setUseConfigSettings',
            ]
        );
        $this->companyPaymentMethodFactory->expects($this->once())->method('create')->willReturn($paymentSettings);
        $paymentSettings->expects($this->once())->method('load')->with($companyId)->willReturn($paymentSettings);
        $paymentSettings->expects($this->once())->method('getId')->willReturn(null);
        $paymentSettings->expects($this->once())->method('setCompanyId')->with($companyId)->willReturnSelf();
        $paymentSettings->expects($this->once())
            ->method('setApplicablePaymentMethod')->with($applicablePayment)->willReturnSelf();
        $paymentSettings->expects($this->once())
            ->method('setAvailablePaymentMethods')->with(implode(',', $availablePayments))->willReturnSelf();
        $paymentSettings->expects($this->once())
            ->method('setUseConfigSettings')->with($useConfigSettings)->willReturnSelf();
        $this->companyPaymentMethodResource->expects($this->once())
            ->method('save')->with($paymentSettings)->willReturn($paymentSettings);
        $extensionAttributes->expects($this->once())
            ->method('setApplicablePaymentMethod')->with(null)->willReturnSelf();
        $extensionAttributes->expects($this->once())
            ->method('setAvailablePaymentMethods')->with(null)->willReturnSelf();
        $extensionAttributes->expects($this->once())
            ->method('setUseConfigSettings')->with(null)->willReturnSelf();
        $company->expects($this->once())->method('setExtensionAttributes')
            ->with($extensionAttributes)->willReturnSelf();
        $this->availablePaymentMethods->execute($company, $initialCompany);
    }
}
