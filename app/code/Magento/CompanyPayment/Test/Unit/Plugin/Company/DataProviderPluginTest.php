<?php

namespace Magento\CompanyPayment\Test\Unit\Plugin\Company;

/**
 * Class DataProviderPluginTest.
 */
class DataProviderPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyPayment\Plugin\Company\DataProviderPlugin
     */
    private $dataProviderPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dataProviderPlugin = $objectManager->getObject(
            \Magento\CompanyPayment\Plugin\Company\DataProviderPlugin::class
        );
    }

    /**
     * Test for method aroundGetSettingsData.
     *
     * @return void
     */
    public function testAroundGetSettingsData()
    {
        $applicableMethod = 'payment1';
        $availableMethods = [$applicableMethod, 'payment2'];
        $useConfigSettings = true;
        $originalSettings = ['original_settings' => 'some settings'];
        $dataProvider = $this->createMock(\Magento\Company\Model\Company\DataProvider::class);
        $closure = function (\Magento\Company\Api\Data\CompanyInterface $company) use ($originalSettings) {
            return $originalSettings;
        };
        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $companyExtension = $this->getMockForAbstractClass(
            \Magento\Company\Api\Data\CompanyExtensionInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getApplicablePaymentMethod', 'getAvailablePaymentMethods', 'getUseConfigSettings']
        );
        $company->expects($this->once())->method('getExtensionAttributes')->willReturn($companyExtension);
        $companyExtension->expects($this->once())->method('getApplicablePaymentMethod')->willReturn($applicableMethod);
        $companyExtension->expects($this->once())->method('getAvailablePaymentMethods')->willReturn($availableMethods);
        $companyExtension->expects($this->once())->method('getUseConfigSettings')->willReturn($useConfigSettings);
        $this->assertEquals(
            array_replace_recursive(
                [
                    'extension_attributes' => [
                        'applicable_payment_method' => $applicableMethod,
                        'available_payment_methods' => $availableMethods,
                        'use_config_settings' => $useConfigSettings,
                    ],
                ],
                $originalSettings
            ),
            $this->dataProviderPlugin->aroundGetSettingsData($dataProvider, $closure, $company)
        );
    }
}
