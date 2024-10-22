<?php
namespace Magento\Analytics\Test\Unit\Model\Connector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\Http\ClientInterface;
use Magento\Analytics\Model\Connector\Http\ResponseResolver;
use Magento\Analytics\Model\Connector\OTPRequest;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * A unit test for testing of the representation of a 'OTP' request.
 */
class OTPRequestTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OTPRequest
     */
    private $subject;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClientMock;

    /**
     * @var AnalyticsToken|\PHPUnit_Framework_MockObject_MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var ResponseResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseResolverMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpClientMock = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->analyticsTokenMock = $this->getMockBuilder(AnalyticsToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseResolverMock = $this->getMockBuilder(ResponseResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->subject = new OTPRequest(
            $this->analyticsTokenMock,
            $this->httpClientMock,
            $this->configMock,
            $this->responseResolverMock,
            $this->loggerMock
        );
    }

    /**
     * Returns test parameters for request.
     *
     * @return array
     */
    private function getTestData()
    {
        return [
            'otp' => 'thisisotp',
            'url' => 'http://www.mystore.com',
            'access-token' => 'thisisaccesstoken',
            'method' => \Magento\Framework\HTTP\ZendClient::POST,
            'body'=> ['access-token' => 'thisisaccesstoken','url' => 'http://www.mystore.com'],
        ];
    }

    /**
     * @return void
     */
    public function testCallSuccess()
    {
        $data = $this->getTestData();

        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(true);
        $this->analyticsTokenMock->expects($this->once())
            ->method('getToken')
            ->willReturn($data['access-token']);

        $this->configMock->expects($this->any())
            ->method('getValue')
            ->willReturn($data['url']);

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                $data['method'],
                $data['url'],
                $data['body']
            )
            ->willReturn(new \Zend_Http_Response(201, []));
        $this->responseResolverMock->expects($this->once())
            ->method('getResult')
            ->willReturn($data['otp']);

        $this->assertEquals(
            $data['otp'],
            $this->subject->call()
        );
    }

    /**
     * @return void
     */
    public function testCallNoAccessToken()
    {
        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(false);

        $this->httpClientMock->expects($this->never())
            ->method('request');

        $this->assertFalse($this->subject->call());
    }

    /**
     * @return void
     */
    public function testCallNoOtp()
    {
        $data = $this->getTestData();

        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(true);
        $this->analyticsTokenMock->expects($this->once())
            ->method('getToken')
            ->willReturn($data['access-token']);

        $this->configMock->expects($this->any())
            ->method('getValue')
            ->willReturn($data['url']);

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                $data['method'],
                $data['url'],
                $data['body']
            )
            ->willReturn(new \Zend_Http_Response(0, []));

        $this->responseResolverMock->expects($this->once())
            ->method('getResult')
            ->willReturn(false);

        $this->loggerMock->expects($this->once())
            ->method('warning');

        $this->assertFalse($this->subject->call());
    }
}
