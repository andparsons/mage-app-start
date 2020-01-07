/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_GiftCardAccount/js/view/payment/gift-card-account',
    'Magento_GiftCardAccount/js/action/set-gift-card-information'
], function (GiftCardAccountView, setGiftCardAction) {
    'use strict';

    return GiftCardAccountView.extend({
        defaults: {
            template: 'Magento_NegotiableQuote/payment/gift-card-account'
        },
        isDisable: window.checkoutConfig.isDiscountFieldLocked && window.checkoutConfig.isNegotiableQuote,

        /** Set gist card. */
        setGiftCard: function () {
            if (this.validate() && !this.isDisable) {
                setGiftCardAction([this.giftCartCode()]);
            }
        }
    });
});
