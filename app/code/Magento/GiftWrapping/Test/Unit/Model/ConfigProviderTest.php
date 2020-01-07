<?php
namespace Magento\GiftWrapping\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GiftWrapping\Model\ConfigProvider;
use Magento\GiftWrapping\Model\ResourceModel\Wrapping\CollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote\Item as QuoteItem;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Checkout\Model\Session|MockObject */
    private $checkoutSession;
    
    /** @var \Magento\Checkout\Model\CartFactory|MockObject */
    private $checkoutCartFactory;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface|MockObject */
    private $productRepository;

    /** @var \Magento\GiftWrapping\Helper\Data|MockObject */
    private $giftWrappingData;

    /** @var \Magento\Store\Model\StoreManagerInterface|MockObject */
    private $storeManager;

    /** @var CollectionFactory|MockObject */
    private $wrappingCollectionFactory;

    /** @var \Magento\GiftWrapping\Model\ResourceModel\Wrapping\Collection|MockObject */
    private $wrappingCollection;

    /** @var \Magento\GiftWrapping\Model\Wrapping|MockObject */
    private $wrappingItem;

    /** @var \Magento\Framework\App\RequestInterface|MockObject */
    private $request;

    /** @var \Psr\Log\LoggerInterface|MockObject */
    private $logger;

    /** @var \Magento\Framework\Pricing\Helper\Data|MockObject */
    private $pricingHelper;

    /** @var \Magento\Framework\UrlInterface|MockObject */
    private $urlBuilder;

    /** @var \Magento\Framework\View\Asset\Repository|MockObject */
    private $assetRepo;

    /** @var ConfigProvider */
    private $provider;

    /** @var \Magento\Quote\Model\QuoteIdMaskFactory|MockObject*/
    private $quoteIdMaskFactory;

    /** @var \Magento\Quote\Model\QuoteIdMask|MockObject */
    private $quoteIdMask;

    /** @var  \Magento\Quote\Model\Cart\Totals|MockObject  */
    private $totalsMock;

    /** @var TaxClassKeyInterfaceFactory|MockObject  */
    private $taxClassKeyFactory;

    /** @var TaxClassKeyInterface|MockObject  */
    private $taxClassKey;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->totalsMock = $this->createPartialMock(
            \Magento\Quote\Model\Cart\Totals::class,
            ['getGwCardPrice', '__wakeUp']
        );
        $this->checkoutSession = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->checkoutCartFactory = $this->createPartialMock(\Magento\Checkout\Model\CartFactory::class, ['create']);
        $this->giftWrappingData = $this->createMock(\Magento\GiftWrapping\Helper\Data::class);
        $this->urlBuilder = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class, [], '', false);
        $this->assetRepo = $this->createMock(\Magento\Framework\View\Asset\Repository::class);

        $this->request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false
        );
        $this->logger = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class, [], '', false);
        $this->pricingHelper = $this->createMock(\Magento\Framework\Pricing\Helper\Data::class);
        $this->productRepository = $this->getMockForAbstractClass(
            \Magento\Catalog\Api\ProductRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->storeManager = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false
        );
        $this->wrappingCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->wrappingCollection = $this->createMock(
            \Magento\GiftWrapping\Model\ResourceModel\Wrapping\Collection::class
        );
        $this->wrappingItem = $this->createPartialMock(
            \Magento\GiftWrapping\Model\Wrapping::class,
            ['getBasePrice', 'setTaxClassKey', 'getImageUrl', 'getId']
        );
        $this->quoteIdMaskFactory = $this->createPartialMock(
            \Magento\Quote\Model\QuoteIdMaskFactory::class,
            ['create']
        );
        $this->quoteIdMask = $this->createPartialMock(\Magento\Quote\Model\QuoteIdMask::class, ['load', 'getMaskedId']);
        $this->taxClassKeyFactory = $this->createPartialMock(
            \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory::class,
            ['create']
        );
        $this->taxClassKey = $this->getMockForAbstractClass(
            \Magento\Tax\Api\Data\TaxClassKeyInterface::class,
            [],
            '',
            false
        );

        $objectManager = new ObjectManager($this);
        $this->provider = $objectManager->getObject(
            ConfigProvider::class,
            [
                'checkoutCartFactory' => $this->checkoutCartFactory,
                'productRepository' => $this->productRepository,
                'giftWrappingData' => $this->giftWrappingData,
                'storeManager' => $this->storeManager,
                'wrappingCollectionFactory' => $this->wrappingCollectionFactory,
                'urlBuilder' => $this->urlBuilder,
                'assetRepo' => $this->assetRepo,
                'request' => $this->request,
                'logger' => $this->logger,
                'checkoutSession' => $this->checkoutSession,
                'pricingHelper' => $this->pricingHelper,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactory,
                'taxClassKeyFactory' => $this->taxClassKeyFactory
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetConfig()
    {
        $address = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $address->expects($this->any())->method('getId')->willReturn(2);
        $shippingAddressMock = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $productMock = $this->createPartialMock(
            Product::class,
            ['getGiftWrappingAvailable', 'getGiftWrappingPrice']
        );
        $quoteItemMock = $this->createPartialMock(
            QuoteItem::class,
            ['getProduct', 'getParentItem', 'getId', 'setTaxClassKey']
        );
        $quoteItemMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $quote = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            [
                'getAllShippingAddresses',
                'getIsMultiShipping',
                'getBillingAddress',
                'hasGwId',
                'getGwId',
                'getTotals',
                'getShippingAddress',
                'getAllItems'
            ]
        );
        $quote->expects($this->atLeastOnce())->method('getAllShippingAddresses')->willReturn([$address]);
        $quote->expects($this->any())->method('getIsMultiShipping')->willReturn(true);
        $quote->expects($this->atLeastOnce())->method('getBillingAddress')->willReturn($address);
        $quote->expects($this->atLeastOnce())->method('hasGwId')->willReturn(true);
        $quote->expects($this->atLeastOnce())->method('getGwId')->willReturn(3);
        $quote->expects($this->once())->method('getShippingAddress')->willReturn($shippingAddressMock);
        $quote->expects($this->once())->method('getAllItems')->willReturn([$quoteItemMock]);
        $this->checkoutSession->expects($this->any())->method('getQuote')->willReturn($quote);

        $cartItems = $this->createCartItemMocks();
        $checkoutCart = $this->createMock(Cart::class);
        $checkoutCart->expects($this->atLeastOnce())->method('getItems')->willReturn($cartItems);
        $this->checkoutCartFactory->expects($this->atLeastOnce())->method('create')->willReturn($checkoutCart);

        $this->productRepository->expects($this->never())->method('getById');

        $this->wrappingItem->expects($this->once())->method('getBasePrice')->willReturn('13');
        $this->wrappingItem->expects($this->atLeastOnce())->method('setTaxClassKey');
        $this->wrappingItem->expects($this->once())->method('getImageUrl')->willReturn('http://image-url.com');
        $this->wrappingItem->expects($this->any())->method('getId')->willReturn(83);
        $this->wrappingCollection->expects($this->once())->method('addStoreAttributesToResult')->willReturnSelf();
        $this->wrappingCollection->expects($this->once())->method('applyStatusFilter')->willReturnSelf();
        $this->wrappingCollection->expects($this->once())->method('applyWebsiteFilter')->willReturnSelf();
        $this->wrappingCollection->expects($this->once())->method('getItems')->willReturn([$this->wrappingItem]);
        $this->wrappingCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->wrappingCollection);

        $this->request->expects($this->once())->method('isSecure')->willReturn(true);

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->expects($this->any())->method('getId')->willReturn(11);
        $store->expects($this->once())->method('getWebsiteId')->willReturn(21);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);

        $this->giftWrappingData->expects($this->atLeastOnce())->method('getPrice')->willReturn(73);
        $this->giftWrappingData->expects($this->any())->method('getPrintedCardPrice')->willReturn(23);
        $this->giftWrappingData->expects($this->atLeastOnce())->method('getWrappingTaxClass')->willReturn('tax-class');
        $this->giftWrappingData->expects($this->atLeastOnce())->method('isGiftWrappingAvailableForOrder');
        $this->giftWrappingData->expects($this->atLeastOnce())->method('isGiftWrappingAvailableForItems');
        $this->giftWrappingData->expects($this->atLeastOnce())->method('allowPrintedCard')->willReturn(true);
        $this->giftWrappingData->expects($this->atLeastOnce())->method('allowGiftReceipt');
        $this->giftWrappingData->expects($this->atLeastOnce())->method('allowGiftReceipt');
        $this->giftWrappingData->expects($this->atLeastOnce())->method('displayCartWrappingBothPrices')
            ->willReturn(false);
        $this->giftWrappingData->expects($this->atLeastOnce())->method('displayCartWrappingIncludeTaxPrice')
            ->willReturn(false);

        $this->quoteIdMask->expects($this->once())->method('load')->willReturnSelf();
        $this->quoteIdMask->expects($this->once())->method('getMaskedId')->willReturn('masked-id');

        $this->quoteIdMaskFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->quoteIdMask);

        $this->taxClassKey->expects($this->atLeastOnce())
            ->method('setType')
            ->with(TaxClassKeyInterface::TYPE_ID)
            ->willReturnSelf();
        $this->taxClassKey->expects($this->atLeastOnce())
            ->method('setValue')
            ->with('tax-class')
            ->willReturnSelf();
        $this->taxClassKeyFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->taxClassKey);
        $this->pricingHelper->expects($this->atLeastOnce())
            ->method('currency');

        $this->provider->getConfig();
    }

    /**
     * Create Cart Item mocks
     *
     * @return array
     */
    private function createCartItemMocks(): array
    {
        $product = $this->createPartialMock(Product::class, ['getGiftWrappingAvailable']);
        $product->method('getGiftWrappingAvailable')->willReturn(true);

        $item = $this->createPartialMock(
            QuoteItem::class,
            ['hasGwId', 'getGwId', 'getId', 'getProduct', 'isDeleted']
        );
        $item->method('isDeleted')->willReturn(false);
        $item->expects($this->once())->method('hasGwId')->willReturn(true);
        $item->expects($this->once())->method('getGwId')->willReturn(13);
        $item->expects($this->once())->method('getId')->willReturn(2);
        $item->expects($this->once())->method('getProduct')->willReturn($product);

        $itemDeleted = $this->createPartialMock(
            QuoteItem::class,
            ['hasGwId', 'getGwId', 'getId', 'getProduct', 'isDeleted']
        );
        $itemDeleted->method('isDeleted')->willReturn(true);
        $itemDeleted->expects($this->never())->method('hasGwId');
        $itemDeleted->expects($this->never())->method('getGwId');
        $itemDeleted->expects($this->never())->method('getId');
        $itemDeleted->expects($this->never())->method('getProduct');

        return [$item, $itemDeleted];
    }
}
