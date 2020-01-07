<?php
namespace Magento\NegotiableQuote\Test\Unit\Model\Validator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for Validator.
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorResultFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\Validator
     */
    private $validator;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->validatorMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->validatorResultFactory = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
    }

    /**
     * Test for validate().
     *
     * @return void
     */
    public function testValidate()
    {
        $validateConfig = [
            'action' => ['action' => 'action']
        ];
        $action = 'action';
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->validator = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Validator\Validator::class,
            [
                'validators' => [$action => $this->validatorMock],
                'validatorResultFactory' => $this->validatorResultFactory,
                'validateConfig' => $validateConfig,
                'action' => $action,
            ]
        );

        $data = [];
        $this->prepareMocks($data);

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->validator->validate($data)
        );
    }

    /**
     * Test for validate() method when his $action property is empty.
     *
     * @return void
     */
    public function testValidateIfActionIsEmpty()
    {
        $validateConfig = [];
        $action = '';
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->validator = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Validator\Validator::class,
            [
                'validators' => [$action => $this->validatorMock],
                'validatorResultFactory' => $this->validatorResultFactory,
                'validateConfig' => $validateConfig,
                'action' => $action,
            ]
        );

        $data = [];
        $this->prepareMocks($data);

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->validator->validate($data)
        );
    }

    /**
     * Prepare mocks for validate() method tests.
     *
     * @param array $data
     * @return void
     */
    private function prepareMocks($data)
    {
        $resultMock = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultMock->expects($this->atLeastOnce())->method('hasMessages')->willReturn(true);
        $this->validatorResultFactory->expects($this->atLeastOnce())->method('create')
            ->willReturn($resultMock);
        $this->validatorMock->expects($this->atLeastOnce())->method('validate')->with($data)
            ->willReturn($resultMock);
    }

    /**
     * Test for validate() method when his $action property is empty.
     *
     * @return void
     */
    public function testValidateIfActionIsNotEmpty()
    {
        $validateConfig = [];
        $action = 'some_action';
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->validator = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Validator\Validator::class,
            [
                'validators' => [$action => $this->validatorMock],
                'validatorResultFactory' => $this->validatorResultFactory,
                'validateConfig' => $validateConfig,
                'action' => $action,
            ]
        );

        $data = [];
        $resultMock = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactory->expects($this->atLeastOnce())->method('create')
            ->willReturn($resultMock);

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->validator->validate($data)
        );
    }
}
