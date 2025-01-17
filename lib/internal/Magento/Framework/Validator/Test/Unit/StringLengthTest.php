<?php

namespace Magento\Framework\Validator\Test\Unit;

/**
 * Test case for \Magento\Framework\Validator\StringLength
 */
class StringLengthTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Validator\StringLength
     */
    protected $_validator;

    protected function setUp()
    {
        $this->_validator = new \Magento\Framework\Validator\StringLength();
    }

    public function testDefaultEncoding()
    {
        $this->assertEquals('UTF-8', $this->_validator->getEncoding());
    }

    /**
     * @dataProvider isValidDataProvider
     * @param string $value
     * @param int $maxLength
     * @param bool $isValid
     */
    public function testIsValid($value, $maxLength, $isValid)
    {
        $this->_validator->setMax($maxLength);
        $this->assertEquals($isValid, $this->_validator->isValid($value));
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            ['строка', 6, true],
            ['строка', 5, false],
            ['string', 6, true],
            ['string', 5, false]
        ];
    }
}
