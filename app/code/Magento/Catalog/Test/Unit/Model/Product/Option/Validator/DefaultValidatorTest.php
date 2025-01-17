<?php

namespace Magento\Catalog\Test\Unit\Model\Product\Option\Validator;

class DefaultValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Option\Validator\DefaultValidator
     */
    protected $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $valueMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeFormatMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $configMock = $this->createMock(\Magento\Catalog\Model\ProductOptions\ConfigInterface::class);
        $storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $priceConfigMock = new \Magento\Catalog\Model\Config\Source\Product\Options\Price($storeManagerMock);
        $this->localeFormatMock = $this->createMock(\Magento\Framework\Locale\FormatInterface::class);

        $config = [
            [
                'label' => 'group label 1',
                'types' => [
                    [
                        'label' => 'label 1.1',
                        'name' => 'name 1.1',
                        'disabled' => false,
                    ],
                ],
            ],
            [
                'label' => 'group label 2',
                'types' => [
                    [
                        'label' => 'label 2.2',
                        'name' => 'name 2.2',
                        'disabled' => true,
                    ],
                ]
            ],
        ];
        $configMock->expects($this->once())->method('getAll')->will($this->returnValue($config));
        $this->validator = new \Magento\Catalog\Model\Product\Option\Validator\DefaultValidator(
            $configMock,
            $priceConfigMock,
            $this->localeFormatMock
        );
    }

    /**
     * Data provider for testIsValidSuccess
     * @return array
     */
    public function isValidTitleDataProvider()
    {
        $mess = ['option required fields' => 'Missed values for option required fields'];
        return [
            ['option_title', 'name 1.1', 'fixed', 10, new \Magento\Framework\DataObject(['store_id' => 1]), [], true],
            ['option_title', 'name 1.1', 'fixed', 10, new \Magento\Framework\DataObject(['store_id' => 0]), [], true],
            [null, 'name 1.1', 'fixed', 10, new \Magento\Framework\DataObject(['store_id' => 1]), [], true],
            [null, 'name 1.1', 'fixed', 10, new \Magento\Framework\DataObject(['store_id' => 0]), $mess, false],
        ];
    }

    /**
     * @param string $title
     * @param string $type
     * @param string $priceType
     * @param \Magento\Framework\DataObject $product
     * @param array $messages
     * @param bool $result
     * @dataProvider isValidTitleDataProvider
     */
    public function testIsValidTitle($title, $type, $priceType, $price, $product, $messages, $result)
    {
        $methods = ['getTitle', 'getType', 'getPriceType', 'getPrice', '__wakeup', 'getProduct'];
        $valueMock = $this->createPartialMock(\Magento\Catalog\Model\Product\Option::class, $methods);
        $valueMock->expects($this->once())->method('getTitle')->will($this->returnValue($title));
        $valueMock->expects($this->any())->method('getType')->will($this->returnValue($type));
        $valueMock->expects($this->once())->method('getPriceType')->will($this->returnValue($priceType));
        $valueMock->expects($this->once())->method('getPrice')->will($this->returnValue($price));
        $valueMock->expects($this->once())->method('getProduct')->will($this->returnValue($product));

        $this->localeFormatMock->expects($this->once())->method('getNumber')->will($this->returnValue($price));

        $this->assertEquals($result, $this->validator->isValid($valueMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }

    /**
     * Data provider for testIsValidFail
     *
     * @return array
     */
    public function isValidFailDataProvider()
    {
        return [
            [new \Magento\Framework\DataObject(['store_id' => 1])],
            [new \Magento\Framework\DataObject(['store_id' => 0])],
        ];
    }

    /**
     * @param \Magento\Framework\DataObject $product
     * @dataProvider isValidFailDataProvider
     */
    public function testIsValidFail($product)
    {
        $methods = ['getTitle', 'getType', 'getPriceType', 'getPrice', '__wakeup', 'getProduct'];
        $valueMock = $this->createPartialMock(\Magento\Catalog\Model\Product\Option::class, $methods);
        $valueMock->expects($this->once())->method('getProduct')->will($this->returnValue($product));
        $valueMock->expects($this->once())->method('getTitle');
        $valueMock->expects($this->any())->method('getType');
        $valueMock->expects($this->once())->method('getPriceType')->will($this->returnValue('some_new_value'));
        $valueMock->expects($this->never())->method('getPrice');
        $messages = [
            'option required fields' => 'Missed values for option required fields',
            'option type' => 'Invalid option type',
            'option values' => 'Invalid option value',
        ];
        $this->assertFalse($this->validator->isValid($valueMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }

    /**
     * Data provider for testValidationNegativePrice
     * @return array
     */
    public function validationPriceDataProvider()
    {
        return [
            ['option_title', 'name 1.1', 'fixed', -12, new \Magento\Framework\DataObject(['store_id' => 1])],
            ['option_title', 'name 1.1', 'fixed', -12, new \Magento\Framework\DataObject(['store_id' => 0])],
            ['option_title', 'name 1.1', 'fixed', 12, new \Magento\Framework\DataObject(['store_id' => 1])],
            ['option_title', 'name 1.1', 'fixed', 12, new \Magento\Framework\DataObject(['store_id' => 0])]
        ];
    }

    /**
     * @param $title
     * @param $type
     * @param $priceType
     * @param $price
     * @param $product
     * @dataProvider validationPriceDataProvider
     */
    public function testValidationPrice($title, $type, $priceType, $price, $product)
    {
        $methods = ['getTitle', 'getType', 'getPriceType', 'getPrice', '__wakeup', 'getProduct'];
        $valueMock = $this->createPartialMock(\Magento\Catalog\Model\Product\Option::class, $methods);
        $valueMock->expects($this->once())->method('getTitle')->will($this->returnValue($title));
        $valueMock->expects($this->exactly(2))->method('getType')->will($this->returnValue($type));
        $valueMock->expects($this->once())->method('getPriceType')->will($this->returnValue($priceType));
        $valueMock->expects($this->once())->method('getPrice')->will($this->returnValue($price));
        $valueMock->expects($this->once())->method('getProduct')->will($this->returnValue($product));

        $this->localeFormatMock->expects($this->once())->method('getNumber')->will($this->returnValue($price));

        $messages = [];
        $this->assertTrue($this->validator->isValid($valueMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }
}
