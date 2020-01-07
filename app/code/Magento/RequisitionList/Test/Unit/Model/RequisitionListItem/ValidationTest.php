<?php

namespace Magento\RequisitionList\Test\Unit\Model\RequisitionListItem;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ValidationTest
 */
class ValidationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Validator\Sku|\PHPUnit_Framework_MockObject_MockObject
     */
    private $skuValidatorMock;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Validation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validation;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->skuValidatorMock = $this->getMockBuilder(
            \Magento\RequisitionList\Model\RequisitionListItem\Validator\Sku::class
        )->disableOriginalConstructor()->getMock();

        $this->validation = $this->objectManagerHelper->getObject(
            \Magento\RequisitionList\Model\RequisitionListItem\Validation::class,
            [
                'validators' => [
                    $this->skuValidatorMock
                ]
            ]
        );
    }

    /**
     * Test isValid
     *
     * @param array $errors
     * @param bool $isValid
     * @return void
     *
     * @dataProvider isValidDataProvider
     */
    public function testIsValid(array $errors, $isValid)
    {
        $itemMock = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->skuValidatorMock->expects($this->any())
            ->method('validate')
            ->willReturn($errors);

        $this->assertEquals(
            $isValid,
            $this->validation->isValid($itemMock)
        );
    }

    /**
     * Data provider for isValid
     *
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            [
                ['error'],
                false
            ],
            [
                [],
                true
            ]
        ];
    }
}
