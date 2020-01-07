<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Quote\Api;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteTotalsInterfaceFactory;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemTotalsInterfaceFactory;

/**
 * Unit test for Magento\NegotiableQuote\Plugin\Quote\Api\JoinNegotiableQuoteTotalsPlugin class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class JoinNegotiableQuoteTotalsPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartRepository;

    /**
     * @var \Magento\Quote\Api\Data\TotalsExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsExtensionFactory;

    /**
     * @var NegotiableQuoteTotalsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteTotalsFactory;

    /**
     * @var \Magento\Quote\Api\Data\TotalsItemExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsItemExtensionFactory;

    /**
     * @var NegotiableQuoteItemTotalsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteItemTotalsFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\TotalsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteTotalsFactory;

    /**
     * @var \Magento\Tax\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxConfig;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Quote\Api\JoinNegotiableQuoteTotalsPlugin
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->cartRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->totalsExtensionFactory = $this->getMockBuilder(\Magento\Quote\Api\Data\TotalsExtensionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->negotiableQuoteTotalsFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteTotalsInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->totalsItemExtensionFactory = $this->getMockBuilder(
            \Magento\Quote\Api\Data\TotalsItemExtensionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->negotiableQuoteItemTotalsFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemTotalsInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->quoteTotalsFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\Quote\TotalsFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->taxConfig = $this->getMockBuilder(\Magento\Tax\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Plugin\Quote\Api\JoinNegotiableQuoteTotalsPlugin::class,
            [
                'cartRepository' => $this->cartRepository,
                'totalsExtensionFactory' => $this->totalsExtensionFactory,
                'negotiableQuoteTotalsFactory' => $this->negotiableQuoteTotalsFactory,
                'totalsItemExtensionFactory' => $this->totalsItemExtensionFactory,
                'negotiableQuoteItemTotalsFactory' => $this->negotiableQuoteItemTotalsFactory,
                'quoteTotalsFactory' => $this->quoteTotalsFactory,
                'taxConfig' => $this->taxConfig,
            ]
        );
    }

    /**
     * Test for afterGet method.
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAfterGet()
    {
        $cartId = 1;
        $totals = [
            'subtotal_excl_tax' => 1,
            'base_subtotal_excl_tax' => 2,
            'subtotal_incl_tax' => 3
        ];
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartTotalRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $result = $this->getMockBuilder(\Magento\Quote\Api\Data\TotalsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $totalsExtension = $this->getMockBuilder(\Magento\Quote\Api\Data\TotalsExtension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $totalsItemExtension = $this->getMockBuilder(
            \Magento\Quote\Api\Data\TotalsItemExtension::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $totalsItem = $this->getMockBuilder(\Magento\Quote\Api\Data\TotalsItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quoteTotals = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\Quote\Totals::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $currency = $this->getMockBuilder(\Magento\Quote\Api\Data\CurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $negotiableQuoteTotals = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteTotalsInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $totalsExtensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\TotalsExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setNegotiableQuoteTotals'])
            ->getMockForAbstractClass();
        $cartItem = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $cartItemExtension = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuoteItem'])
            ->getMockForAbstractClass();
        $negotiableQuoteItem = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $negotiableQuoteItemTotals = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemTotalsInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $totalsItemExtensionInterface =$this->getMockBuilder(
            \Magento\Quote\Api\Data\TotalsItemExtensionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['setNegotiableQuoteItemTotals'])
            ->getMockForAbstractClass();
        $this->cartRepository->expects($this->once())->method('get')->with($cartId, ['*'])->willReturn($quote);
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->atLeastOnce())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->once())->method('getIsRegularQuote')->willReturn(true);
        $result->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturnOnConsecutiveCalls(null, $totalsExtensionAttributes);
        $this->totalsExtensionFactory->expects($this->once())->method('create')->willReturn($totalsExtension);
        $result->expects($this->once())->method('setExtensionAttributes')->with($totalsExtension)->willReturnSelf();
        $result->expects($this->atLeastOnce())->method('getItems')->willReturn([$totalsItem]);
        $totalsItem->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturnOnConsecutiveCalls(null, $totalsItemExtensionInterface);
        $this->totalsItemExtensionFactory->expects($this->once())->method('create')->willReturn($totalsItemExtension);
        $totalsItem->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($totalsItemExtension)
            ->willReturnSelf();
        $this->quoteTotalsFactory->expects($this->once())
            ->method('create')
            ->with(['quote' => $quote])
            ->willReturn($quoteTotals);
        $negotiableQuote->expects($this->once())
            ->method('getStatus')
            ->willReturn(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN);
        $quote->expects($this->once())->method('getCreatedAt')->willReturn(date('Y-m-d H:i:s'));
        $quote->expects($this->once())->method('getUpdatedAt')->willReturn(date('Y-m-d H:i:s'));
        $quote->expects($this->once())->method('getCustomer')->willReturn($customer);
        $customer->expects($this->once())->method('getGroupId')->willReturn(1);
        $quote->expects($this->once())->method('getItemsCount')->willReturn(1);
        $quote->expects($this->atLeastOnce())->method('getCurrency')->willReturn($currency);
        $currency->expects($this->atLeastOnce())->method('getBaseToQuoteRate')->willReturn(0.75);
        $quoteTotals->expects($this->exactly(2))
            ->method('getTotalCost')
            ->withConsecutive([true], [false])
            ->willReturn(0);
        $quoteTotals->expects($this->exactly(2))
            ->method('getCatalogTotalPrice')
            ->withConsecutive([false], [true])
            ->willReturn(100);
        $quoteTotals->expects($this->exactly(2))
            ->method('getOriginalTaxValue')
            ->withConsecutive([false], [true])
            ->willReturn(10);
        $quoteTotals->expects($this->exactly(2))
            ->method('getCatalogTotalPriceWithTax')
            ->withConsecutive([false], [true])
            ->willReturn(110);
        $negotiableQuote->expects($this->once())->method('getNegotiatedPriceType')->willReturn(null);
        $negotiableQuote->expects($this->once())->method('getNegotiatedPriceValue')->willReturn(null);
        $this->negotiableQuoteTotalsFactory->expects($this->once())
            ->method('create')
            ->willReturn($negotiableQuoteTotals);
        $totalsExtensionAttributes->expects($this->once())
            ->method('setNegotiableQuoteTotals')
            ->with($negotiableQuoteTotals)
            ->willReturnSelf();
        $quote->expects($this->once())->method('getItems')->willReturn([$cartItem]);
        $cartItem->expects($this->once())->method('getItemId')->willReturn(1);
        $totalsItem->expects($this->once())->method('getItemId')->willReturn(1);
        $cartItem->expects($this->once())->method('getExtensionAttributes')->willReturn($cartItemExtension);
        $cartItemExtension->expects($this->once())->method('getNegotiableQuoteItem')->willReturn($negotiableQuoteItem);
        $quote->expects($this->once())->method('getStoreId')->willReturn(1);
        $this->taxConfig->expects($this->once())->method('priceIncludesTax')->with(1)->willReturn(false);
        $quoteTotals->expects($this->once())->method('getItemCost')->with($cartItem)->willReturn(100);
        $negotiableQuoteItem->expects($this->atLeastOnce())->method('getOriginalPrice')->willReturn(100);
        $negotiableQuoteItem->expects($this->atLeastOnce())->method('getOriginalTaxAmount')->willReturn(10);
        $negotiableQuoteItem->expects($this->once())->method('getOriginalDiscountAmount')->willReturn(0);
        $this->negotiableQuoteItemTotalsFactory->expects($this->once())
            ->method('create')
            ->willReturn($negotiableQuoteItemTotals);
        $totalsItemExtensionInterface->expects($this->once())->method('setNegotiableQuoteItemTotals')
            ->with($negotiableQuoteItemTotals)
            ->willReturnSelf();
        $quoteTotals->expects($this->any())->method('getSubtotalWithoutTax')->willReturnMap([
            [true, $totals['subtotal_excl_tax']],
            [false, $totals['base_subtotal_excl_tax']]
        ]);
        $quoteTotals->expects($this->once())->method('getSubtotalWithTax')->with(true)
            ->willReturn($totals['subtotal_incl_tax']);
        $result->expects($this->once())->method('setSubtotal')->with($totals['subtotal_excl_tax'])->willReturnSelf();
        $result->expects($this->once())->method('setBaseSubtotal')->with($totals['base_subtotal_excl_tax'])
            ->willReturnSelf();
        $result->expects($this->once())->method('setSubtotalInclTax')->with($totals['subtotal_incl_tax'])
            ->willReturnSelf();
        $this->assertEquals($result, $this->plugin->afterGet($subject, $result, $cartId));
    }
}
