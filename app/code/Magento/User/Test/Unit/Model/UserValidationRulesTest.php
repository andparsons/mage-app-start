<?php
namespace Magento\User\Test\Unit\Model;

use Magento\User\Model\UserValidationRules;

class UserValidationRulesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Validator\DataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var UserValidationRules
     */
    private $rules;

    protected function setUp()
    {
        $this->validator = $this->createMock(\Magento\Framework\Validator\DataObject::class);
        $this->rules = new UserValidationRules();
    }

    public function testAddUserInfoRules()
    {
        $this->validator->expects($this->exactly(4))->method('addRule')->willReturn($this->validator);
        $this->assertSame($this->validator, $this->rules->addUserInfoRules($this->validator));
    }

    public function testAddPasswordRules()
    {
        $this->validator->expects($this->exactly(3))->method('addRule')->willReturn($this->validator);
        $this->assertSame($this->validator, $this->rules->addPasswordRules($this->validator));
    }

    public function testAddPasswordConfirmationRule()
    {
        $this->validator->expects($this->once())->method('addRule')->willReturn($this->validator);
        $this->assertSame($this->validator, $this->rules->addPasswordConfirmationRule($this->validator, ''));
    }
}
