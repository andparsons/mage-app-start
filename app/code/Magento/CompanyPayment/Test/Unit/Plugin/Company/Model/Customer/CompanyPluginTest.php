<?php

namespace Magento\CompanyPayment\Test\Unit\Plugin\Company\Model\Customer;

/**
 * Class CompanyPluginTest.
 */
class CompanyPluginTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\CompanyPayment\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \Magento\CompanyPayment\Plugin\Company\Model\Customer\CompanyPlugin
     */
    private $companyPlugin;

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
        $this->config = $this->createMock(\Magento\CompanyPayment\Model\Config::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyPlugin = $objectManager->getObject(
            \Magento\CompanyPayment\Plugin\Company\Model\Customer\CompanyPlugin::class,
            [
                'companyPaymentMethodResource' => $this->companyPaymentMethodResource,
                'companyPaymentMethodFactory' => $this->companyPaymentMethodFactory,
                'config' => $this->config,
            ]
        );
    }

    /**
     * Test for method afterCreateCompany.
     *
     * @return void
     */
    public function testAfterCreateCompany()
    {
        $companyId = 1;
        $availablePaymentMethods = ['payment1'];
        $subject = $this->createMock(\Magento\Company\Model\Customer\Company::class);
        $result = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $paymentSettings = $this->createMock(\Magento\CompanyPayment\Model\CompanyPaymentMethod::class);
        $this->companyPaymentMethodFactory->expects($this->once())->method('create')->willReturn($paymentSettings);
        $result->expects($this->once())->method('getId')->willReturn($companyId);
        $paymentSettings->expects($this->once())->method('setCompanyId')->with($companyId)->willReturnSelf();
        $paymentSettings->expects($this->once())->method('setApplicablePaymentMethod')->with('0')->willReturnSelf();
        $paymentSettings->expects($this->once())->method('setUseConfigSettings')->with('1')->willReturnSelf();
        $this->config->expects($this->once())->method('isSpecificApplicableMethodApplied')->willReturn(true);
        $this->config->expects($this->exactly(2))
            ->method('getAvailablePaymentMethods')->willReturn($availablePaymentMethods);
        $paymentSettings->expects($this->once())
            ->method('setAvailablePaymentMethods')->with($availablePaymentMethods)->willReturnSelf();
        $this->companyPaymentMethodResource->expects($this->once())
            ->method('save')->with($paymentSettings)->willReturn($paymentSettings);
        $this->assertEquals($result, $this->companyPlugin->afterCreateCompany($subject, $result));
    }
}
