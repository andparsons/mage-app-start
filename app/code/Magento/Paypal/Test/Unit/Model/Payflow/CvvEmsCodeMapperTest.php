<?php
namespace Magento\Paypal\Test\Unit\Model\Payflow;

use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Info;
use Magento\Paypal\Model\Payflow\CvvEmsCodeMapper;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CvvEmsCodeMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CvvEmsCodeMapper
     */
    private $mapper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->mapper = new CvvEmsCodeMapper();
    }

    /**
     * Checks different variations for cvv codes mapping.
     *
     * @covers \Magento\Paypal\Model\Payflow\CvvEmsCodeMapper::getCode
     * @param string $cvvCode
     * @param string $expected
     * @dataProvider getCodeDataProvider
     */
    public function testGetCode($cvvCode, $expected)
    {
        /** @var OrderPaymentInterface|MockObject $orderPayment */
        $orderPayment = $this->getMockBuilder(OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderPayment->expects(self::once())
            ->method('getMethod')
            ->willReturn(Config::METHOD_PAYFLOWPRO);

        $orderPayment->expects(self::once())
            ->method('getAdditionalInformation')
            ->willReturn([Info::PAYPAL_CVV2MATCH => $cvvCode]);

        self::assertEquals($expected, $this->mapper->getCode($orderPayment));
    }

    /**
     * Checks a test case, when payment order is not Payflow payment method.
     *
     * @covers \Magento\Paypal\Model\Payflow\CvvEmsCodeMapper::getCode
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "some_payment" does not supported by Payflow CVV mapper.
     */
    public function testGetCodeWithException()
    {
        /** @var OrderPaymentInterface|MockObject $orderPayment */
        $orderPayment = $this->getMockBuilder(OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderPayment->expects(self::exactly(2))
            ->method('getMethod')
            ->willReturn('some_payment');

        $this->mapper->getCode($orderPayment);
    }

    /**
     * Gets variations of cvv codes and expected mapping result.
     *
     * @return array
     */
    public function getCodeDataProvider()
    {
        return [
            ['cvvCode' => '', 'expected' => 'P'],
            ['cvvCode' => null, 'expected' => 'P'],
            ['cvvCode' => 'Y', 'expected' => 'M'],
            ['cvvCode' => 'N', 'expected' => 'N'],
            ['cvvCode' => 'X', 'expected' => 'P']
        ];
    }
}
