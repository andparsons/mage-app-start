<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\History;

/**
 * Class ValidatorTest
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\History\Validator
     */
    private $validator;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->validator = $objectManager->getObject(\Magento\NegotiableQuote\Model\History\Validator::class);
    }

    /**
     * Test for validate() method
     *
     * @return void
     */
    public function testValidate()
    {
        $expectedResult = [
            '"Negotiable quote ID" is required. Enter and try again.',
            '"Author ID" is required. Enter and try again.'
        ];
        $objectMock = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasData'])
            ->getMock();
        $objectMock->expects($this->any())
            ->method('hasData')
            ->willReturn(false);
        $result = $this->validator->validate($objectMock);
        $this->assertEquals($expectedResult, $result);
    }
}
