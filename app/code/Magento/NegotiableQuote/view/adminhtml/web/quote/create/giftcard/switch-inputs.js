/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global $ */
define(['Magento_NegotiableQuote/catalog/product/composite/configure'], function () {
    'use strict';

    return function () {
        var giftcardAmount = $('giftcard_amount'),
            giftcardAmountFields = $('giftcard_amount_input_fields'),
            customAmount = $('giftcard_amount_input');

        /**
         * Add slide toggle for gift card fields
         */
        window.productConfigure.giftcardConfig.switchGiftCardInputs = function () {
            var value;

            if (!giftcardAmount || !giftcardAmountFields) {
                return;
            }

            value = giftcardAmount.options[giftcardAmount.selectedIndex].value;

            if (value == 'custom') { //eslint-disable-line eqeqeq
                giftcardAmountFields.show();

                if (customAmount) {
                    customAmount.disabled = false;
                }
            } else {
                giftcardAmountFields.hide();

                if (customAmount) {
                    customAmount.disabled = true;
                }
            }
        };

        if (giftcardAmount && giftcardAmountFields) {
            giftcardAmountFields.hide();
        }

        window.productConfigure.giftcardConfig.switchGiftCardInputs();
    };
});
