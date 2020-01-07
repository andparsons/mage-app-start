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

    return function (setPaymentInformation) {
        return wrapper.wrap(setPaymentInformation, function (originalAction, messageContainer, paymentData) {
            var serviceUrl, payload;

            if (window.checkoutConfig.isNegotiableQuote) {
                serviceUrl = urlBuilder.createUrl('/negotiable-carts/:cartId/set-payment-information', {
                    cartId: quote.getQuoteId()
                });
                payload = {
                    cartId: quote.getQuoteId(),
                    paymentMethod: paymentData,
                    billingAddress: quote.billingAddress()
                };

                fullScreenLoader.startLoader();

                return storage.post(
                    serviceUrl, JSON.stringify(payload)
                ).fail(function (response) {
                    errorProcessor.process(response, messageContainer);
                }).always(fullScreenLoader.stopLoader);
            }

            return originalAction(messageContainer, paymentData);
        });
    };
});
