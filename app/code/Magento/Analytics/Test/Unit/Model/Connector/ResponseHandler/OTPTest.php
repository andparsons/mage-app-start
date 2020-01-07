<?php
namespace Magento\Analytics\Test\Unit\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\Connector\ResponseHandler\OTP;

class OTPTest extends \PHPUnit\Framework\TestCase
{
    public function testHandleResult()
    {
        $OTPHandler = new OTP();
        $this->assertFalse($OTPHandler->handleResponse([]));
        $expectedOtp = 123;
        $this->assertEquals($expectedOtp, $OTPHandler->handleResponse(['otp' => $expectedOtp]));
    }
}
