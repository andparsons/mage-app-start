<?php

namespace Magento\Company\Test\Unit\Block\Company\Account;

/**
 * Class CreateTest
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private $formData = 'form_data';

    /**
     * @var \Magento\Company\Model\CountryInformationProvider|\PHPUnit\Framework\MockObject_MockObject
     */
    private $countryInformationProvider;

    /**
     * @var \Magento\Customer\Helper\Address|\PHPUnit\Framework\MockObject_MockObject
     */
    private $addressHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\Company\Block\Company\Account\Create
     */
    private $create;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->countryInformationProvider = $this->createMock(\Magento\Company\Model\CountryInformationProvider::class);
        $this->addressHelper = $this->createMock(\Magento\Customer\Helper\Address::class);
        $this->scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->create = $objectManager->getObject(
            \Magento\Company\Block\Company\Account\Create::class,
            [
                'countryInformationProvider' => $this->countryInformationProvider,
                'addressHelper' => $this->addressHelper,
                '_urlBuilder' => $this->urlBuilder,
                '_scopeConfig' => $this->scopeConfig,
                'data' => [],
            ]
        );
    }

    /**
     * Test method for getConfig.
     */
    public function testGetConfig()
    {
        $value = 'general/region/display_all/all';
        $this->scopeConfig->expects($this->once())->method('getValue')->willReturn($value);
        $this->assertEquals($value, $this->create->getConfig($value));
    }

    /**
     * Test method for getPostActionUrl.
     */
    public function testGetPostActionUrl()
    {
        $value = '*/account/createPost';
        $this->urlBuilder->expects($this->any())->method('getUrl')->willReturn($value);
        $this->assertEquals($value, $this->create->getPostActionUrl());
    }

    /**
     * Test method for getCountriesList.
     */
    public function testGetCountriesList()
    {
        $data = ['test'];
        $this->countryInformationProvider->expects($this->any())->method('getCountriesList')->willReturn($data);
        $this->assertEquals($data, $this->create->getCountriesList());
    }

    /**
     * Test method for getFormData.
     *
     * @param string|null $data
     * @param string|null $additionalData
     * @dataProvider getFromDataDataProvider
     */
    public function testGetFormData($data, $additionalData = null)
    {
        $formData = new \Magento\Framework\DataObject();
        $formData->setRegion($data);
        if ($formData->getRegion() === null) {
            $formData->setRegionId($additionalData);
        }
        $this->create->setData($this->formData, $formData);
        $this->assertSame($formData, $this->create->getFormData());
    }

    /**
     * Data provider for testGetFormData.
     *
     * @return array
     */
    public function getFromDataDataProvider()
    {
        return [
            ['California'],
            [null, 56]
        ];
    }

    /**
     * Test method for getDefaultCountryId.
     */
    public function testGetDefaultCountryId()
    {
        $path = 'test/path';
        $this->scopeConfig->expects($this->once())->method('getValue')->willReturn($path);
        $this->assertEquals($path, $this->create->getDefaultCountryId());
    }

    /**
     * Test method for getAttributeValidationClass.
     */
    public function testGetAttributeValidationClass()
    {
        $attributeCode = 'test';
        $this->addressHelper->expects($this->once())
            ->method('getAttributeValidationClass')
            ->with($attributeCode)
            ->willReturn('testAttributeValidationClass');
        $this->assertEquals('testAttributeValidationClass', $this->create->getAttributeValidationClass($attributeCode));
    }
}
