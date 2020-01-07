/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mage/utils/wrapper',
    'mage/storage',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader'
], function (wrapper, storage, quote, urlBuilder, errorProcessor, fullScreenLoader) {
    'use strict';

    return function (placeOrder) {
        return wrapper.wrap(placeOrder, function (originalAction, paymentData, messageContainer) {
            var serviceUrl, payload,
                url = '/negotiable-carts/:quoteId/payment-information?isNegotiableQuote=true';

            if (window.checkoutConfig.isNegotiableQuote) {
                serviceUrl = urlBuilder.createUrl(url, {
                    quoteId: quote.getQuoteId()
                });
                payload = {
                    cartId: quote.getQuoteId(),
                    billingAddress: quote.billingAddress(),
                    paymentMethod: paymentData
                };

                fullScreenLoader.startLoader();

                return storage.post(
                    serviceUrl, JSON.stringify(payload)
                ).fail(function (response) {
                    errorProcessor.process(response, messageContainer);
                    fullScreenLoader.stopLoader();
                });
            }

            return originalAction(paymentData, messageContainer);
        });
    };
});
