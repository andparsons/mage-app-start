<?php
namespace Magento\CheckoutAddressSearchNegotiableQuote\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\NegotiableQuote\Model\NegotiableQuoteConfigProvider;
use Magento\CheckoutAddressSearch\Model\Config;

/**
 * Modify shipping address search configuration if negotiable quote address is locked.
 */
class ShippingAddressLayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var NegotiableQuoteConfigProvider
     */
    private $configProvider;

    /**
     * Address Search configuration.
     *
     * @var Config
     */
    private $addressSearchConfig;

    /**
     * @param NegotiableQuoteConfigProvider $configProvider
     * @param Config $addressSearchConfig
     */
    public function __construct(
        NegotiableQuoteConfigProvider $configProvider,
        Config $addressSearchConfig
    ) {
        $this->configProvider = $configProvider;
        $this->addressSearchConfig = $addressSearchConfig;
    }

    /**
     * @inheritdoc
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout): array
    {
        if (!$this->isQuoteAddressLockedOnCheckout()) {
            return $jsLayout;
        }
        $addressListConfiguration = &$jsLayout['components']['checkout']['children']['steps']['children']
        ['shipping-step']['children']['shippingAddress']['children']['address-list'];

        if (isset($addressListConfiguration['children']) && $this->addressSearchConfig->isEnabledAddressSearch()) {
            unset($addressListConfiguration['children']['selectShippingAddressModal']);

            $addressListConfiguration['children']['addressDefault']['isChangeAddressVisible'] =
                !$this->isQuoteAddressLockedOnCheckout();
            $addressListConfiguration['children']['addressDefault']['component'] =
                'Magento_CheckoutAddressSearchNegotiableQuote/js/view/shipping-address/selected';
        }

        return $jsLayout;
    }

    /**
     * Check if quote address is locked on checkout.
     *
     * @return bool
     */
    private function isQuoteAddressLockedOnCheckout(): bool
    {
        $config = $this->configProvider->getConfig();
        return !empty($config['isQuoteAddressLocked']);
    }
}
