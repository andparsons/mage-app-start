/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            negotiableQuoteTabs: 'Magento_NegotiableQuote/js/quote/tabs'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/payment/default': {
                'Magento_NegotiableQuote/js/view/payment/default-mixin': true
            },
            'Magento_Checkout/js/model/resource-url-manager': {
                'Magento_NegotiableQuote/js/model/resource-url-manager-mixin': true
            },
            'Magento_Checkout/js/model/shipping-service': {
                'Magento_NegotiableQuote/js/model/shipping-service-mixin': true
            },
            'Magento_Checkout/js/action/get-payment-information': {
                'Magento_NegotiableQuote/js/action/get-payment-information-mixin': true
            },
            'Magento_Checkout/js/action/place-order': {
                'Magento_NegotiableQuote/js/action/place-order-mixin': true
            },
            'Magento_Checkout/js/action/set-billing-address': {
                'Magento_NegotiableQuote/js/action/set-billing-address-mixin': true
            },
            'Magento_Checkout/js/action/set-payment-information': {
                'Magento_NegotiableQuote/js/action/set-payment-information-mixin': true
            },
            'Magento_GiftCardAccount/js/action/set-gift-card-information': {
                'Magento_NegotiableQuote/js/action/set-gift-card-information-mixin': true
            },
            'Magento_GiftCardAccount/js/action/remove-gift-card-from-quote': {
                'Magento_NegotiableQuote/js/action/remove-gift-card-from-quote-mixin': true
            }
        }
    }
};
