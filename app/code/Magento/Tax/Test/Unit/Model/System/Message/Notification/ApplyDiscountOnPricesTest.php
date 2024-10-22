<?php

namespace Magento\Tax\Test\Unit\Model\System\Message\Notification;

use Magento\Tax\Model\Config as TaxConfig;
use Magento\Tax\Model\System\Message\Notification\ApplyDiscountOnPrices as ApplyDiscountOnPricesNotification;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;

/**
 * Test class for @see \Magento\Tax\Model\System\Message\Notification\ApplyDiscountOnPrices
 *
 * @SuppressWarnings(PHPMD)
 */
class ApplyDiscountOnPricesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ApplyDiscountOnPricesNotification
     */
    private $applyDiscountOnPricesNotification;

    /**
     * @var StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var UrlInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var TaxConfig | \PHPUnit_Framework_MockObject_MockObject
     */
    private $taxConfigMock;

    protected function setUp()
    {
        parent::setUp();

        $websiteMock = $this->createMock(WebsiteInterface::class);
        $websiteMock->expects($this->any())->method('getName')->willReturn('testWebsiteName');
        $storeMock = $this->getMockForAbstractClass(
            StoreInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getWebsite', 'getName']
        );
        $storeMock->expects($this->any())->method('getName')->willReturn('testStoreName');
        $storeMock->expects($this->any())->method('getWebsite')->willReturn($websiteMock);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeManagerMock->expects($this->any())->method('getStores')->willReturn([$storeMock]);

        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->taxConfigMock = $this->createMock(TaxConfig::class);
        $this->applyDiscountOnPricesNotification = (new ObjectManager($this))->getObject(
            ApplyDiscountOnPricesNotification::class,
            [
                'storeManager' => $this->storeManagerMock,
                'urlBuilder' => $this->urlBuilderMock,
                'taxConfig' => $this->taxConfigMock,
            ]
        );
    }

    /**
     * @dataProvider dataProviderIsDisplayed
     */
    public function testIsDisplayed(
        $isWrongApplyDiscountSettingIgnored,
        $priceIncludesTax,
        $applyTaxAfterDiscount,
        $discountTax,
        $expectedResult
    ) {
        $this->taxConfigMock->expects($this->any())->method('isWrongApplyDiscountSettingIgnored')
            ->willReturn($isWrongApplyDiscountSettingIgnored);
        $this->taxConfigMock->expects($this->any())->method('priceIncludesTax')->willReturn($priceIncludesTax);
        $this->taxConfigMock->expects($this->any())->method('applyTaxAfterDiscount')
            ->willReturn($applyTaxAfterDiscount);
        $this->taxConfigMock->expects($this->any())->method('discountTax')->willReturn($discountTax);

        $this->assertEquals($expectedResult, $this->applyDiscountOnPricesNotification->isDisplayed());
    }

    /**
     * @return array
     */
    public function dataProviderIsDisplayed()
    {
        return [
            [
                false, // $isWrongApplyDiscountSettingIgnored,
                false, // $priceIncludesTax,
                true, // $applyTaxAfterDiscount,
                true, // $discountTax,
                true // $expectedResult
            ],
            [
                false, // $isWrongApplyDiscountSettingIgnored,
                false, // $priceIncludesTax,
                true, // $applyTaxAfterDiscount,
                false, // $discountTax,
                false // $expectedResult
            ],
            [
                false, // $isWrongApplyDiscountSettingIgnored,
                false, // $priceIncludesTax,
                false, // $applyTaxAfterDiscount,
                true, // $discountTax,
                false // $expectedResult
            ],
            [
                false, // $isWrongApplyDiscountSettingIgnored,
                true, // $priceIncludesTax,
                true, // $applyTaxAfterDiscount,
                true, // $discountTax,
                false // $expectedResult
            ],
            [
                true, // $isWrongApplyDiscountSettingIgnored,
                false, // $priceIncludesTax,
                true, // $applyTaxAfterDiscount,
                true, // $discountTax,
                false // $expectedResult
            ]
        ];
    }

    public function testGetText()
    {
        $this->taxConfigMock->expects($this->any())->method('isWrongApplyDiscountSettingIgnored')->willReturn(false);

        $this->taxConfigMock->expects($this->any())->method('priceIncludesTax')->willReturn(false);
        $this->taxConfigMock->expects($this->any())->method('applyTaxAfterDiscount')->willReturn(true);
        $this->taxConfigMock->expects($this->any())->method('discountTax')->willReturn(true);

        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->with('tax/tax/ignoreTaxNotification', ['section' => 'apply_discount'])
            ->willReturn('http://example.com');
        $this->applyDiscountOnPricesNotification->isDisplayed();
        $this->assertEquals(
            '<strong>To apply the discount on prices including tax and apply the tax after discount, '
            . 'set Catalog Prices to “Including Tax”. </strong><p>Store(s) affected: testWebsiteName '
            . '(testStoreName)</p><p>Click on the link to '
            . '<a href="http://example.com">ignore this notification</a></p>',
            $this->applyDiscountOnPricesNotification->getText()
        );
    }
}
