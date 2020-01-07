<?php
declare(strict_types=1);

namespace Magento\DataServices\Model;

use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\Quote;

/**
 * Model for Cart Context
 */
class CartContext implements CartContextInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ProductHelper
     */
    private $productHelper;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @param CheckoutSession $checkoutSession
     * @param ProductHelper $productHelper
     * @param CheckoutHelper $checkoutHelper
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ProductHelper $productHelper,
        CheckoutHelper $checkoutHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productHelper = $productHelper;
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * @inheritDoc
     */
    public function getContextData() : array
    {
        $quote = $this->checkoutSession->getQuote();
        $items = $this->getCartItems($quote);

        $context = [
            'cartId' => (int) $quote->getId(),
            'itemsCount' => count($items),
            'subtotalExcludingTax' => (float) $quote->getBaseSubtotal(),
            'subtotalIncludingTax' => (float) $quote->getSubtotal(),
            'possibleOnepageCheckout' => $this->checkoutHelper->canOnepageCheckout(),
            'giftMessageSelected' => $quote->getGiftMessageId() ? true : false,
            'giftWrappingSelected' => false,
            'items' => $items
        ];

        return $context;
    }

    /**
     * Get cart items from quote
     *
     * @param Quote $quote
     * @return array
     */
    private function getCartItems(Quote $quote) : array
    {
        $context = [];
        $items = $quote->getAllVisibleItems();

        foreach ($items as $item) {
            $context[] = [
                'cartItemId' => (int) $item->getItemId(),
                'productSku' => $item->getSku(),
                'productName' => $item->getName(),
                'qty' => $item->getQty(),
                'basePrice' => (float) $item->getBasePrice(),
                'offerPrice' => (float) $item->getBasePrice(),
                'mainImageUrl' => $this->productHelper->getImageUrl($item->getProduct())
            ];
        }

        return $context;
    }
}
