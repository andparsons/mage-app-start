<?php

namespace Magento\CompanyCredit\Test\Unit\Model\History\Source;

/**
 * Class OperationTest.
 */
class OperationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Model\History\Source\Operation
     */
    private $operation;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->operation = $objectManager->getObject(
            \Magento\CompanyCredit\Model\History\Source\Operation::class
        );
    }

    /**
     * Test for method getAllOptions.
     *
     * @return void
     */
    public function testGetAllOptions()
    {
        $expectedResult = array_map(
            function ($label, $value) {
                return ['value' => $value, 'label' => $label];
            },
            \Magento\CompanyCredit\Model\History\Source\Operation::getOptionArray(),
            array_keys(\Magento\CompanyCredit\Model\History\Source\Operation::getOptionArray())
        );
        $this->assertEquals($expectedResult, $this->operation->getAllOptions());
    }
}
