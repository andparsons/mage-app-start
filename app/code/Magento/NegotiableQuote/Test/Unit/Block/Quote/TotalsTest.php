<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Quote;

/**
 * Class TotalsTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TotalsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postDataHelper;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteHelper;

    /**
     * @var \Magento\Tax\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxConfig;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\TotalsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteTotalsFactory;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuote;

    /**
     * @var \Magento\NegotiableQuote\Block\Quote\Totals|\PHPUnit_Framework_MockObject_MockObject
     */
    private $totals;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->quoteRepository = $this->getMockForAbstractClass(
            \Magento\Quote\Api\CartRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['get']
        );
        $this->postDataHelper = $this->createMock(\Magento\Framework\Data\Helper\PostHelper::class);
        $this->negotiableQuoteHelper = $this->createMock(\Magento\NegotiableQuote\Helper\Quote::class);
        $this->taxConfig =
            $this->createPartialMock(\Magento\Tax\Model\Config::class, ['displaySalesTaxWithGrandTotal']);
        $this->restriction = $this->createMock(\Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class);
        $this->quoteTotalsFactory =
            $this->createPartialMock(\Magento\NegotiableQuote\Model\Quote\TotalsFactory::class, ['create']);
        $this->quote = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartInterface::class,
            [],
            '',
            false,
            true,
            true,
            [
                'getCatalogTotalPriceWithoutTax',
                'getCatalogTotalPriceWithTax',
                'getCartTotalDiscount',
                'getOriginalTaxValue',
                'getCatalogTotalPrice',
                'getSubtotal',
                'getGrandTotal',
                'collectTotals',
                'getExtensionAttributes',
                'getShippingAddress',
                'getCurrency',
                'getTaxValue'
            ]
        );
        $this->negotiableQuote = $this->getMockForAbstractClass(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class,
            ['getShippingPrice'],
            '',
            false,
            true,
            true,
            []
        );
        $this->storeManager = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);
        $this->scopeConfig = $this->getMockForAbstractClass(\Magento\Framework\App\Config\ScopeConfigInterface::class);
    }

    /**
     * Tests getTotals() method.
     *
     * @return void
     */
    public function testGetTotals()
    {
        $this->storeManager->expects($this->any())->method('getStore');
        $this->scopeConfig->expects($this->once())->method('getValue');
        $this->quoteTotalsFactory->expects($this->once())->method('create')->willReturn($this->quote);

        $quoteCurrencyCode = 'USD';
        $baseToQuoteRate = 1.4;
        $quoteCurrency = $this->getMockBuilder(\Magento\Quote\Api\Data\CurrencyInterface::class)
            ->setMethods([
                'getBaseToQuoteRate',
                'getQuoteCurrencyCode'
            ])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $quoteCurrency->expects($this->any())->method('getBaseToQuoteRate')->willReturn($baseToQuoteRate);
        $quoteCurrency->expects($this->any())->method('getQuoteCurrencyCode')->willReturn($quoteCurrencyCode);

        $this->quote->expects($this->exactly(4))->method('getCurrency')->willReturn($quoteCurrency);
        $this->quote->expects($this->once())->method('getCatalogTotalPriceWithoutTax');
        $this->quote->expects($this->once())->method('getCatalogTotalPriceWithTax');
        $this->quote->expects($this->exactly(2))->method('getCartTotalDiscount');
        $this->quote->expects($this->once())->method('getOriginalTaxValue');
        $this->quote->expects($this->once())->method('getCatalogTotalPrice');
        $this->quote->expects($this->once())->method('getTaxValue');

        $this->taxConfig->expects($this->any())->method('displaySalesTaxWithGrandTotal');
        $this->negotiableQuoteHelper->expects($this->any())->method('resolveCurrentQuote')->willReturn($this->quote);
        $cartExtension = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getParam', 'getNegotiableQuote']
        );
        $this->quote->expects($this->any())->method('getExtensionAttributes')->willReturn($cartExtension);
        $cartExtension->expects($this->any())->method('getNegotiableQuote')->willReturn($this->negotiableQuote);
        $this->negotiableQuote->expects($this->once())->method('getShippingPrice');
        $this->quote->expects($this->exactly(2))->method('getGrandTotal');
        $this->createSUT();
        $this->totals->getTotals();
    }

    /**
     * Create totals object.
     *
     * @return void
     */
    protected function createSUT()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->totals = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Quote\Totals::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'postDataHelper' => $this->postDataHelper,
                'negotiableQuoteHelper' => $this->negotiableQuoteHelper,
                'restriction' => $this->restriction,
                'taxConfig' => $this->taxConfig,
                'quoteTotalsFactory' => $this->quoteTotalsFactory,
                '_storeManager' => $this->storeManager,
                '_scopeConfig' => $this->scopeConfig,
                'data' => [],
            ]
        );
    }
}
