<?php

namespace Magento\CompanyPayment\Test\Unit\Plugin\Company;

/**
 * Class CompanyRepositoryPluginTest.
 */
class CompanyRepositoryPluginTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Company\Api\Data\CompanyExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyExtensionFactory;

    /**
     * @var \Magento\CompanyPayment\Plugin\Company\CompanyRepositoryPlugin
     */
    private $companyRepositoryPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyPaymentMethodResource =
            $this->createMock(\Magento\CompanyPayment\Model\ResourceModel\CompanyPaymentMethod::class);
        $this->companyPaymentMethodFactory =
            $this->createPartialMock(\Magento\CompanyPayment\Model\CompanyPaymentMethodFactory::class, ['create']);
        $this->companyExtensionFactory =
            $this->createPartialMock(\Magento\Company\Api\Data\CompanyExtensionFactory::class, ['create']);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyRepositoryPlugin = $objectManager->getObject(
            \Magento\CompanyPayment\Plugin\Company\CompanyRepositoryPlugin::class,
            [
                'companyPaymentMethodResource' => $this->companyPaymentMethodResource,
                'companyPaymentMethodFactory' => $this->companyPaymentMethodFactory,
                'companyExtensionFactory' => $this->companyExtensionFactory,
            ]
        );
    }

    /**
     * Test for method afterGet.
     *
     * @return void
     */
    public function testAfterGet()
    {
        $companyId = 1;
        $applicablePayment = 'payment1';
        $availablePayments = [$applicablePayment, 'payment2'];
        $useConfigSettings = true;
        $companyRepository = $this->createMock(\Magento\Company\Api\CompanyRepositoryInterface::class);
        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $company->expects($this->once())->method('getId')->willReturn($companyId);
        $paymentMethod = $this->createPartialMock(
            \Magento\CompanyPayment\Model\CompanyPaymentMethod::class,
            ['getApplicablePaymentMethod', 'getAvailablePaymentMethods', 'getUseConfigSettings', 'load', 'getId']
        );
        $this->companyPaymentMethodFactory->expects($this->once())->method('create')->willReturn($paymentMethod);
        $paymentMethod->expects($this->once())->method('load')->with($companyId)->willReturn($paymentMethod);
        $paymentMethod->expects($this->once())->method('getId')->willReturn(2);
        $companyExtension = $this->getMockForAbstractClass(
            \Magento\Company\Api\Data\CompanyExtensionInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['setApplicablePaymentMethod', 'setAvailablePaymentMethods', 'setUseConfigSettings']
        );
        $this->companyExtensionFactory->expects($this->once())->method('create')->willReturn($companyExtension);
        $paymentMethod->expects($this->once())->method('getApplicablePaymentMethod')->willReturn($applicablePayment);
        $paymentMethod->expects($this->once())->method('getAvailablePaymentMethods')->willReturn($availablePayments);
        $paymentMethod->expects($this->once())->method('getUseConfigSettings')->willReturn($useConfigSettings);
        $companyExtension->expects($this->once())
            ->method('setApplicablePaymentMethod')->with($applicablePayment)->willReturnSelf();
        $companyExtension->expects($this->once())
            ->method('setAvailablePaymentMethods')->with($availablePayments)->willReturnSelf();
        $companyExtension->expects($this->once())
            ->method('setUseConfigSettings')->with($useConfigSettings)->willReturnSelf();
        $company->expects($this->once())->method('setExtensionAttributes')->with($companyExtension)->willReturnSelf();
        $this->assertEquals(
            $company,
            $this->companyRepositoryPlugin->afterGet($companyRepository, $company)
        );
    }
}
