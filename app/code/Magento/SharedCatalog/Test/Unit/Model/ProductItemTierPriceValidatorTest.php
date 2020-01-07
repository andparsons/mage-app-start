<?php

namespace Magento\SharedCatalog\Test\Unit\Model;

/**
 * ProductItemTierPriceValidator unit test.
 */
class ProductItemTierPriceValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemTierPriceValidator
     */
    private $productItemTierPriceValidator;

    /**
     * Set up.
     *
     * @return void.
     */
    protected function setUp()
    {
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->productItemTierPriceValidator = $objectManager->getObject(
            \Magento\SharedCatalog\Model\ProductItemTierPriceValidator::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'allowedProductTypes' => ['simple', 'bundle'],
            ]
        );
    }

    /**
     * Test validateDuplicates.
     *
     * @param array $tierPrices
     * @param bool $validationResult
     * @return void
     * @dataProvider validateDuplicatesDataProvider
     */
    public function testValidateDuplicates(array $tierPrices, $validationResult)
    {
        $this->assertEquals($validationResult, $this->productItemTierPriceValidator->validateDuplicates($tierPrices));
    }

    /**
     * DataProvider validateDuplicates.
     *
     * @return array
     */
    public function validateDuplicatesDataProvider()
    {
        return [
            [
                [],
                true
            ],
            [
                [
                    ['delete' => true],
                    ['qty' => 1, 'website_id' => 1],
                    ['qty' => 1, 'website_id' => 1]
                ],
                false
            ],
            [
                [
                    ['delete' => true],
                    ['qty' => 1, 'website_id' => 1],
                    ['qty' => 1, 'website_id' => 0]
                ],
                false
            ],
        ];
    }

    /**
     * Test isTierPriceApplicable method.
     *
     * @param bool $expectedResult
     * @param string $productType
     * @return void
     * @dataProvider isTierPriceApplicableDataProvider
     */
    public function testIsTierPriceApplicable($expectedResult, $productType)
    {
        $this->assertEquals(
            $expectedResult,
            $this->productItemTierPriceValidator->isTierPriceApplicable($productType)
        );
    }

    /**
     * Test for canChangePrice method.
     *
     * @return void
     */
    public function testCanChangePrice()
    {
        $websiteId = null;
        $prices = [0 => ['prices_data0'], 1 => ['prices_data1']];
        $this->scopeConfig->expects($this->once())
            ->method('getValue')->with('catalog/price/scope', 'store')
            ->willReturn(\Magento\Store\Model\Store::PRICE_SCOPE_GLOBAL);
        $this->assertTrue($this->productItemTierPriceValidator->canChangePrice($prices, $websiteId));
    }

    /**
     * Data provider for isTierPriceApplicable method.
     *
     * @return array
     */
    public function isTierPriceApplicableDataProvider()
    {
        return [
            [true, 'simple'],
            [true, 'bundle'],
            [false, 'configurable'],
            [false, 'virtual'],
            [false, 'giftcard'],
        ];
    }
}
