<?php
namespace Magento\Cybersource\Test\Unit\Gateway\Request\SilentOrder;

use Magento\Cybersource\Gateway\Request\SilentOrder\SignCompositeDecorator;
use Magento\Cybersource\Gateway\Request\SilentOrder\CcDataBuilder;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SignCompositeDecoratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager\TMapFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $tMapFactory;

    /**
     * @var \Magento\Framework\ObjectManager\TMap | \PHPUnit_Framework_MockObject_MockObject
     */
    private $tmap;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTime;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var ScopeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scope;

    protected function setUp()
    {
        $this->tMapFactory = $this->getMockBuilder(\Magento\Framework\ObjectManager\TMapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->tmap = $this->getMockBuilder(\Magento\Framework\ObjectManager\TMap::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tMapFactory->expects(static::once())
            ->method('create')
            ->willReturn($this->tmap);

        $this->dateTimeFactory = $this->getMockBuilder(\Magento\Framework\Intl\DateTimeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTime = $this->getMockBuilder(\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeFactory->expects(static::once())
            ->method('create')
            ->willReturn($this->dateTime);

        $this->scope = $this->createMock(ScopeInterface::class);

        $this->configMock = $this->getMockBuilder(\Magento\Payment\Gateway\ConfigInterface::class)
            ->getMockForAbstractClass();
    }

    /**
     * Run test for build method.
     *
     * @param string $scope
     * @param string $isMultidomain
     * @param string $areaPrefix
     * @return void
     * @dataProvider buildDataProvider
     * @throws LocalizedException
     */
    public function testBuild(string $scope, string $isMultidomain, string $areaPrefix)
    {
        $subject = ['payment' => $this->getPaymentDO()];

        $this->tmap->expects(static::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($this->getExpectedBuilders()));
        $this->dateTime->expects(static::once())
            ->method('format')
            ->with(SignCompositeDecorator::SIGNED_DATE_TIME_FORMAT)
            ->willReturn('2013-09-17T08:17:07Z');
        $this->scope->method('getCurrentScope')
            ->willReturn($scope);
        $this->configMock->expects($this->at(0))
            ->method('getValue')
            ->with('is_multidomain', 1)
            ->willReturn($isMultidomain);
        $this->configMock->expects($this->at(1))
            ->method('getValue')
            ->with($areaPrefix . 'secret_key', 1)
            ->willReturn('SECRET');

        $signBuilder = new SignCompositeDecorator(
            $this->dateTimeFactory,
            $this->configMock,
            ['amount', 'cc'],
            $this->tMapFactory,
            $this->scope
        );

        $result = $signBuilder->build($subject);
        static::assertSame($this->getExpectedResult(), $result);
    }

    /**
     * Dataprovider
     *
     * @return array
     */
    public function buildDataProvider()
    {
        return [
            ['adminhtml', '1', 'admin_'],
            ['adminhtml', '0', ''],
            ['frontend', '1', ''],
            ['frontend', '0', ''],
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPaymentDO()
    {
        $order = $this->getMockBuilder(\Magento\Payment\Gateway\Data\OrderAdapterInterface::class)
            ->getMock();
        $paymentDO = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class)
            ->getMock();

        $paymentDO->expects(static::once())
            ->method('getOrder')
            ->willReturn($order);

        $order->expects(static::once())
            ->method('getStoreId')
            ->willReturn(1);

        return $paymentDO;
    }

    /**
     * @return array
     */
    private function getExpectedBuilders()
    {
        $amountBuilder = $this->getMockBuilder(\Magento\Payment\Gateway\Request\BuilderInterface::class)
            ->getMockForAbstractClass();
        $ccBuilder = $this->getMockBuilder(\Magento\Payment\Gateway\Request\BuilderInterface::class)
            ->getMockForAbstractClass();

        $amountBuilder->expects(static::once())
            ->method('build')
            ->willReturn(['amount' => '0.00', 'currency' => 'USD']);
        $ccBuilder->expects(static::once())
            ->method('build')
            ->willReturn(
                [
                    CcDataBuilder::CARD_TYPE => '001',
                    CcDataBuilder::CARD_NUMBER => '',
                    CcDataBuilder::CARD_EXPIRY_DATE => '',
                    CcDataBuilder::CARD_CVN => ''
                ]
            );

        return [$ccBuilder, $amountBuilder];
    }

    /**
     * @return array
     */
    private function getExpectedResult()
    {
        return [
            'amount' => '0.00',
            'currency' => 'USD',
            SignCompositeDecorator::SIGNED_DATE_TIME => '2013-09-17T08:17:07Z',
            SignCompositeDecorator::UNSIGNED_FIELD_NAMES => implode(
                ',',
                [
                    CcDataBuilder::CARD_TYPE,
                    CcDataBuilder::CARD_NUMBER,
                    CcDataBuilder::CARD_EXPIRY_DATE,
                    CcDataBuilder::CARD_CVN
                ]
            ),
            SignCompositeDecorator::SIGNED_FIELD_NAMES => implode(
                ',',
                [
                    'amount',
                    'currency',
                    SignCompositeDecorator::SIGNED_DATE_TIME,
                    SignCompositeDecorator::UNSIGNED_FIELD_NAMES,
                    SignCompositeDecorator::SIGNED_FIELD_NAMES
                ]
            ),
            CcDataBuilder::CARD_TYPE => '001',
            CcDataBuilder::CARD_NUMBER => '',
            CcDataBuilder::CARD_EXPIRY_DATE => '',
            CcDataBuilder::CARD_CVN => '',
            SignCompositeDecorator::SIGNATURE => $this->getSignature(),
            'store_id' => 1
        ];
    }

    /**
     * @return string
     */
    private function getSignature()
    {
        return
            base64_encode(
                hash_hmac(
                    'sha256',
                    sprintf(
                        '%s=%s,%s=%s,%s=%s,%s=%s,%s=%s',
                        'amount',
                        '0.00',
                        'currency',
                        'USD',
                        SignCompositeDecorator::SIGNED_DATE_TIME,
                        '2013-09-17T08:17:07Z',
                        SignCompositeDecorator::UNSIGNED_FIELD_NAMES,
                        implode(
                            ',',
                            [
                                CcDataBuilder::CARD_TYPE,
                                CcDataBuilder::CARD_NUMBER,
                                CcDataBuilder::CARD_EXPIRY_DATE,
                                CcDataBuilder::CARD_CVN
                            ]
                        ),
                        SignCompositeDecorator::SIGNED_FIELD_NAMES,
                        implode(
                            ',',
                            [
                                'amount',
                                'currency',
                                SignCompositeDecorator::SIGNED_DATE_TIME,
                                SignCompositeDecorator::UNSIGNED_FIELD_NAMES,
                                SignCompositeDecorator::SIGNED_FIELD_NAMES
                            ]
                        )
                    ),
                    'SECRET',
                    true
                )
            );
    }
}
