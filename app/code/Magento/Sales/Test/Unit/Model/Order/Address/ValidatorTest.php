<?php

namespace Magento\Sales\Test\Unit\Model\Order\Address;

/**
 * Class ValidatorTest
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Address\Validator
     */
    protected $validator;

    /**
     * @var \Magento\Sales\Model\Order\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    /**
     * @var \Magento\Directory\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryHelperMock;

    /**
     * @var \Magento\Directory\Model\CountryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $countryFactoryMock;

    /**
     * Mock order address model
     */
    protected function setUp()
    {
        $this->addressMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Address::class,
            ['hasData', 'getEmail', 'getAddressType', '__wakeup']
        );
        $this->directoryHelperMock = $this->createMock(\Magento\Directory\Helper\Data::class);
        $this->countryFactoryMock = $this->createMock(\Magento\Directory\Model\CountryFactory::class);
        $eavConfigMock = $this->createMock(\Magento\Eav\Model\Config::class);
        $attributeMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute::class);
        $attributeMock->expects($this->any())
            ->method('getIsRequired')
            ->willReturn(true);
        $eavConfigMock->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attributeMock));
        $this->validator = new \Magento\Sales\Model\Order\Address\Validator(
            $this->directoryHelperMock,
            $this->countryFactoryMock,
            $eavConfigMock
        );
    }

    /**
     * Run test validate
     *
     * @param $addressData
     * @param $email
     * @param $addressType
     * @param $expectedWarnings
     * @dataProvider providerAddressData
     */
    public function testValidate($addressData, $email, $addressType, $expectedWarnings)
    {
        $this->addressMock->expects($this->any())
            ->method('hasData')
            ->will($this->returnValueMap($addressData));
        $this->addressMock->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue($email));
        $this->addressMock->expects($this->once())
            ->method('getAddressType')
            ->will($this->returnValue($addressType));
        $actualWarnings = $this->validator->validate($this->addressMock);
        $this->assertEquals($expectedWarnings, $actualWarnings);
    }

    /**
     * Provides address data for tests
     *
     * @return array
     */
    public function providerAddressData()
    {
        return [
            [
                [
                    ['parent_id', true],
                    ['postcode', true],
                    ['lastname', true],
                    ['street', true],
                    ['city', true],
                    ['email', true],
                    ['telephone', true],
                    ['country_id', true],
                    ['firstname', true],
                    ['address_type', true],
                    ['company', 'Magento'],
                    ['fax', '222-22-22'],
                ],
                'co@co.co',
                'billing',
                [],
            ],
            [
                [
                    ['parent_id', true],
                    ['postcode', true],
                    ['lastname', true],
                    ['street', false],
                    ['city', true],
                    ['email', true],
                    ['telephone', true],
                    ['country_id', true],
                    ['firstname', true],
                    ['address_type', true],
                    ['company', 'Magento'],
                    ['fax', '222-22-22'],
                ],
                'co.co.co',
                'coco-shipping',
                [
                    '"Street" is required. Enter and try again.',
                    'Email has a wrong format',
                    'Address type doesn\'t match required options'
                ]
            ]
        ];
    }
}
