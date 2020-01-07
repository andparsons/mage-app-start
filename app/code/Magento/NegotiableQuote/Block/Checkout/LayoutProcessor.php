<?php

namespace Magento\NegotiableQuote\Block\Checkout;

use Magento\NegotiableQuote\Model\NegotiableQuoteConfigProvider;

/**
 * Class LayoutProcessor
 */
class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     * Cart items component template
     */
    const TEMPLATE_CART_ITEMS = 'Magento_NegotiableQuote/checkout/summary/cart-items';

    /**
     * @var NegotiableQuoteConfigProvider
     */
    protected $configProvider;

    /**
     * @param NegotiableQuoteConfigProvider $configProvider
     */
    public function __construct(NegotiableQuoteConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        if (!$this->isNegotiableQuoteCheckout()) {
            return $jsLayout;
        }

        if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']
            ['summary']['children']['totals']['children']['subtotal']['config']['title'])
        ) {
            $subtotalConfig = &$jsLayout['components']['checkout']['children']['sidebar']['children']
            ['summary']['children']['totals']['children']['subtotal']['config'];
            $subtotalConfig['title'] = __('Quote Subtotal');
        }

        if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']
            ['summary']['children']['cart_items'])
        ) {
            $cartItemsComponent = &$jsLayout['components']['checkout']['children']['sidebar']['children']
            ['summary']['children']['cart_items'];
            $cartItemsComponent['template'] = self::TEMPLATE_CART_ITEMS;
        }

        return $jsLayout;
    }

    /**
     * @return bool
     */
    protected function isNegotiableQuoteCheckout()
    {
        $config = $this->configProvider->getConfig();
        return !empty($config['isNegotiableQuote']);
    }
}
