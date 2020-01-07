<?php
declare(strict_types=1);

namespace Magento\Company\Test\Unit\Model\Company\Source\Provider;

use Magento\Company\Model\Company\Source\Provider\CustomerAttributeOptions;

/**
 * Class GenderTest.
 */
class CustomerAttributeOptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeFactory;

    /**
     * @var CustomerAttributeOptions|\PHPUnit_Framework_MockObject_MockObject
     */
    private $provider;

    /**
     * @var string
     */
    private $attributeCode = 'gender';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->attributeFactory = $this->createPartialMock(
            \Magento\Eav\Model\Entity\AttributeFactory::class,
            [
                'create',
            ]
        );

        $this->provider = $this->getMockForAbstractClass(
            CustomerAttributeOptions::class,
            [$this->attributeFactory]
        );
    }

    /**
     * @covers \Magento\Company\Model\Company\Source\Provider\CustomerAttributeOptions::loadOptions
     *
     * @return void
     */
    public function testLoadOptions(): void
    {
        $label = 'label';
        $value = 'value';
        $result = [['label' => $label, 'value' => $value]];
        $attribute = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute::class,
            [
                'getOptions',
                'loadByCode',
            ]
        );
        $option = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\Option::class,
            [
                'getLabel',
                'getValue',
            ]
        );
        $this->attributeFactory->expects($this->once())->method('create')->willReturn($attribute);
        $attribute->expects($this->once())
            ->method('loadByCode')
            ->with('customer', $this->attributeCode);
        $attribute->expects($this->once())->method('getOptions')->willReturn([$option]);
        $option->expects($this->once())->method('getLabel')->willReturn($label);
        $option->expects($this->once())->method('getValue')->willReturn($value);
        $this->assertEquals(
            $this->provider->loadOptions($this->attributeCode),
            $result
        );
    }
}
