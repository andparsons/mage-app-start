<?php
namespace Magento\Customer\Test\Unit\Model\Address\Validator;

class PostcodeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Check postcode test
     *
     * @test
     */
    public function testIsValid()
    {
        $countryUs = 'US';
        $countryUa = 'UK';
        $helperMock = $this->getMockBuilder(\Magento\Directory\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helperMock->expects($this->any())
            ->method('isZipCodeOptional')
            ->willReturnMap(
                [
                    [$countryUs, true],
                    [$countryUa, false],
                ]
            );

        $validator = new \Magento\Customer\Model\Address\Validator\Postcode($helperMock);
        $this->assertTrue($validator->isValid($countryUs, ''));
        $this->assertFalse($validator->isValid($countryUa, ''));
        $this->assertTrue($validator->isValid($countryUa, '123123'));
    }
}
