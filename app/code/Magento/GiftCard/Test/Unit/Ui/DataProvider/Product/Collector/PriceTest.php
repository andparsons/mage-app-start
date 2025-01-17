<?php
namespace Magento\GiftCard\Test\Unit\Ui\DataProvider\Product\Collector;

use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface;
use Magento\GiftCard\Ui\DataProvider\Product\Collector\Price;
use Magento\Catalog\Model\ProductRender\FormattedPriceInfoBuilder;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterfaceFactory;

class PriceTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\GiftCard\Ui\DataProvider\Product\Collector\Price */
    protected $model;

    /** @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $priceCurrencyMock;

    /** @var PriceInfoInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $priceInfoFactory;

    /** @var FormattedPriceInfoBuilder|\PHPUnit_Framework_MockObject_MockObject */
    private $formattedPriceInfoBuilder;

    protected function setUp()
    {
        $this->priceCurrencyMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->priceInfoFactory = $this->getMockBuilder(PriceInfoInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->formattedPriceInfoBuilder = $this->getMockBuilder(FormattedPriceInfoBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Price(
            $this->priceCurrencyMock,
            $this->priceInfoFactory,
            $this->formattedPriceInfoBuilder
        );
    }

    public function testCollect()
    {
        $giftcardAmounts = [
            ['website_value' => 12],
            ['website_value' => 13]
        ];
        $priceInfo = $this->getMockBuilder(PriceInfoInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productRender = $this->getMockBuilder(ProductRenderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $productMock = $this->getMockBuilder(Product::class)
            ->setMethods(['getGiftcardAmounts', 'getTypeId'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('giftcard');
        $productRender->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfo);
        $priceInfo->expects($this->once())
            ->method('setMinimalPrice')
            ->with(12);
        $priceInfo->expects($this->once())
            ->method('setMaxPrice')
            ->with(13);
        $productRender->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);
        $productRender->expects($this->once())
            ->method('getCurrencyCode')
            ->willReturn('UAH');
        $this->formattedPriceInfoBuilder->expects($this->once())
            ->method('build')
            ->with($priceInfo, 1, 'UAH');
        $productMock->expects($this->once())
            ->method('getGiftcardAmounts')
            ->willReturn($giftcardAmounts);
        $productRender->expects($this->once())
            ->method('setPriceInfo')
            ->with($priceInfo);

        $this->model->collect($productMock, $productRender);
    }
}
