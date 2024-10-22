<?php
namespace Magento\Payment\Test\Unit\Gateway\Validator;

use Magento\Payment\Gateway\Validator\ValidatorComposite;
use Magento\Payment\Gateway\Validator\ValidatorInterface;

class ValidatorCompositeTest extends \PHPUnit\Framework\TestCase
{
    public function testValidate()
    {
        $validationSubject = [];
        $validator1 = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $validator2 = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $tMapFactory = $this->getMockBuilder(\Magento\Framework\ObjectManager\TMapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $tMap = $this->getMockBuilder(\Magento\Framework\ObjectManager\TMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => [
                        'validator1' => ValidatorInterface::class,
                        'validator2' => ValidatorInterface::class
                    ],
                    'type' => ValidatorInterface::class
                ]
            )
            ->willReturn($tMap);
        $tMap->expects(static::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$validator1, $validator2]));

        $resultSuccess = $this->getMockBuilder(\Magento\Payment\Gateway\Validator\ResultInterface::class)
            ->getMockForAbstractClass();
        $resultSuccess->expects(static::once())
            ->method('isValid')
            ->willReturn(true);
        $resultFail = $this->getMockBuilder(\Magento\Payment\Gateway\Validator\ResultInterface::class)
            ->getMockForAbstractClass();
        $resultFail->expects(static::once())
            ->method('isValid')
            ->willReturn(false);
        $resultFail->expects(static::once())
            ->method('getFailsDescription')
            ->willReturn(['Fail']);
        $resultFail->expects(static::once())
            ->method('getErrorCodes')
            ->willReturn(['abc123']);

        $validator1->expects(static::once())
            ->method('validate')
            ->with($validationSubject)
            ->willReturn($resultSuccess);
        $validator2->expects(static::once())
            ->method('validate')
            ->with($validationSubject)
            ->willReturn($resultFail);

        $compositeResult = $this->getMockBuilder(\Magento\Payment\Gateway\Validator\ResultInterface::class)
            ->getMockForAbstractClass();
        $resultFactory = $this->getMockBuilder(\Magento\Payment\Gateway\Validator\ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'isValid' => false,
                    'failsDescription' => ['Fail'],
                    'errorCodes' => ['abc123']
                ]
            )
            ->willReturn($compositeResult);

        $validatorComposite = new ValidatorComposite(
            $resultFactory,
            $tMapFactory,
            [
                'validator1' => ValidatorInterface::class,
                'validator2' => ValidatorInterface::class
            ]
        );
        static::assertSame($compositeResult, $validatorComposite->validate($validationSubject));
    }

    public function testValidateChainBreaksCorrectly()
    {
        $validationSubject = [];
        $validator1 = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $validator2 = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $tMapFactory = $this->getMockBuilder(\Magento\Framework\ObjectManager\TMapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $tMap = $this->getMockBuilder(\Magento\Framework\ObjectManager\TMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tMapFactory->expects($this->once())
            ->method('create')
            ->with(
                [
                    'array' => [
                        'validator1' => ValidatorInterface::class,
                        'validator2' => ValidatorInterface::class
                    ],
                    'type' => ValidatorInterface::class
                ]
            )
            ->willReturn($tMap);
        $tMap->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$validator1, $validator2]));

        $resultFail = $this->getMockBuilder(\Magento\Payment\Gateway\Validator\ResultInterface::class)
            ->getMockForAbstractClass();
        $resultFail->expects($this->once())
            ->method('isValid')
            ->willReturn(false);
        $resultFail->expects($this->once())
            ->method('getFailsDescription')
            ->willReturn(['Fail']);
        $resultFail->expects($this->once())
            ->method('getErrorCodes')
            ->willReturn(['abc123']);

        $validator1->expects($this->once())
            ->method('validate')
            ->with($validationSubject)
            ->willReturn($resultFail);

        // Assert this is never called
        $validator2->expects($this->never())
            ->method('validate');

        $compositeResult = $this->getMockBuilder(\Magento\Payment\Gateway\Validator\ResultInterface::class)
            ->getMockForAbstractClass();
        $resultFactory = $this->getMockBuilder(\Magento\Payment\Gateway\Validator\ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultFactory->expects($this->once())
            ->method('create')
            ->with(
                [
                    'isValid' => false,
                    'failsDescription' => ['Fail'],
                    'errorCodes' => ['abc123']
                ]
            )
            ->willReturn($compositeResult);

        $validatorComposite = new ValidatorComposite(
            $resultFactory,
            $tMapFactory,
            [
                'validator1' => ValidatorInterface::class,
                'validator2' => ValidatorInterface::class
            ],
            ['validator1']
        );
        $this->assertSame($compositeResult, $validatorComposite->validate($validationSubject));
    }
}
