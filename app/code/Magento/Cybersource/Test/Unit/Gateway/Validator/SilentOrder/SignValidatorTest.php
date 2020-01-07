<?php
namespace Magento\Cybersource\Test\Unit\Gateway\Validator\SilentOrder;

use Magento\Cybersource\Gateway\Validator\SilentOrder\SignValidator;
use Magento\Framework\Config\ScopeInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Validator\Result;

class SignValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var SignValidator
     */
    private $validator;

    /**
     * @var ConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var PaymentDataObjectInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentDO;

    /**
     * @var ScopeInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $scope;

    /**
     * @var OrderAdapterInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderAdapter;

    protected function setUp()
    {
        $this->resultFactory = $this->getMockBuilder(
            \Magento\Payment\Gateway\Validator\ResultInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(\Magento\Payment\Gateway\ConfigInterface::class)
            ->getMockForAbstractClass();
        $this->paymentDO = $this->getMockBuilder(
            \Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class
        )->getMockForAbstractClass();
        $this->orderAdapter = $this->getMockBuilder(
            \Magento\Payment\Gateway\Data\OrderAdapterInterface::class
        )->getMockForAbstractClass();

        $this->scope = $this->createMock(ScopeInterface::class);

        $this->validator = new SignValidator($this->resultFactory, $this->config, $this->scope);
    }

    public function testValidateNoFieldsToValidate()
    {
        $validationSubject = [
            'payment' => $this->paymentDO,
            'response' => []
        ];

        $this->resultFactory->expects(static::once())
            ->method('create')
            ->with(['isValid' => false, 'failsDescription' => [__('Gateway validation error')],'errorCodes' => []])
            ->willReturn(new Result(false, [__('Gateway validation error')]));

        $result = $this->validator->validate($validationSubject);
        static::assertFalse($result->isValid());
    }

    public function testValidateSignParseException()
    {
        $validationSubject = [
            'payment' => $this->paymentDO,
            'response' => [
                SignValidator::SIGNED_FIELD_NAMES => 'existingField,nonExistingField',
                SignValidator::SIGNATURE => 'bla',
                'existingField' => 'value'
            ]
        ];

        $this->paymentDO->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderAdapter);
        $this->orderAdapter->method('getStoreId')
            ->willReturn(1);

        $this->resultFactory->expects(static::once())
            ->method('create')
            ->with(['isValid' => false, 'failsDescription' => [__('Gateway validation error')], 'errorCodes' => []])
            ->willReturn(new Result(false, [__('Gateway validation error')]));

        $result = $this->validator->validate($validationSubject);
        static::assertFalse($result->isValid());
    }

    /**
     * @param string $scope
     * @param string $isMultidomain
     * @param string $areaPrefix
     * @dataProvider buildValidateProvider
     */
    public function testValidate(string $scope, string $isMultidomain, string $areaPrefix)
    {
        $validationSubject = [
            'payment' => $this->paymentDO,
            'response' => [
                SignValidator::SIGNED_FIELD_NAMES => 'field',
                SignValidator::SIGNATURE => 'DtfdsHBHfTDNtuphHKwwRlSklaBhY5kyiyaFWVp2AsA=',
                'field' => 'value'
            ]
        ];

        $this->scope->method('getCurrentScope')
            ->willReturn($scope);
        $this->paymentDO->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderAdapter);
        $this->orderAdapter->method('getStoreId')
            ->willReturn(1);
        $this->config->expects($this->at(0))
            ->method('getValue')
            ->with('is_multidomain', 1)
            ->willReturn($isMultidomain);
        $this->config->expects($this->at(1))
            ->method('getValue')
            ->with($areaPrefix . 'secret_key', 1)
            ->willReturn('KEY');

        $this->resultFactory->expects(static::once())
            ->method('create')
            ->with(['isValid' => true, 'failsDescription' => [],'errorCodes' => []])
            ->willReturn(new Result(true, []));

        $result = $this->validator->validate($validationSubject);
        static::assertTrue($result->isValid());
    }

    /**
     * Dataprovider
     *
     * @return array
     */
    public function buildValidateProvider()
    {
        return [
            ['adminhtml', '1', 'admin_'],
            ['adminhtml', '0', ''],
            ['frontend', '1', ''],
            ['frontend', '0', ''],
        ];
    }
}
