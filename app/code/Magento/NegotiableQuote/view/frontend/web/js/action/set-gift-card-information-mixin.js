/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/utils/wrapper',
    'mage/storage',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_GiftCardAccount/js/model/payment/gift-card-messages'
], function (
    $,
    wrapper,
    storage,
    quote,
    urlBuilder,
    errorProcessor,
    fullScreenLoader,
    getPaymentInformationAction,
    totals,
    messageList
) {
    'use strict';

    return function (setGiftCardInformation) {
        return wrapper.wrap(setGiftCardInformation, function (originalAction, giftCardCode) {
            var message = 'Gift Card ' + giftCardCode + ' was added.',
                serviceUrl, payload;

            if (window.checkoutConfig.isNegotiableQuote) {
                serviceUrl = urlBuilder.createUrl('/negotiable-carts/:cartId/giftCards', {
                    cartId: quote.getQuoteId()
                });
                payload = {
                    cartId: quote.getQuoteId(),
                    giftCardAccountData: {
                        'gift_cards': giftCardCode
                    }
                };

                messageList.clear();
                fullScreenLoader.startLoader();

                return storage.post(
                    serviceUrl, JSON.stringify(payload)
                ).done(function (response) {
                    /**
                     * Callback for getPaymentInformationAction.
                     */
                    var onGetPaymentInformationAction = function () {
                        totals.isLoading(false);
                    };

                    if (response) {
                        totals.isLoading(true);
                        $.when(getPaymentInformationAction()).done(onGetPaymentInformationAction);
                        messageList.addSuccessMessage({
                            'message': message
                        });
                    }
                }).fail(function (response) {
                    totals.isLoading(false);
                    errorProcessor.process(response, messageList);
                }).always(fullScreenLoader.stopLoader);
            }

            return originalAction(giftCardCode);
        });
    };
});
