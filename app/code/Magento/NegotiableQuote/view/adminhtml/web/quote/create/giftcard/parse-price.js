/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_GiftCard/catalog/product/composite/fieldset/validation-rules',
    'Magento_NegotiableQuote/catalog/product/composite/configure'
], function () {
    'use strict';

    return function (param) {
        /**
         * Set params for gift card
         */
        window.productConfigure.giftcardConfig.minAllowedAmount = param.config.min;
        window.productConfigure.giftcardConfig.maxAllowedAmount = param.config.max;

        /**
         * @param {String} value
         * @return {Number}
         */
        window.productConfigure.giftcardConfig.parsePrice = function (value) {
            var separatorComa, separatorDot;

            value = value.replace('\'', '').replace(' ', '');
            separatorComa = value.indexOf(',');
            separatorDot = value.indexOf('.');

            if (separatorComa != -1 && separatorDot != -1) { //eslint-disable-line eqeqeq
                if (separatorComa > separatorDot) {
                    value = value.replace('.', '').replace(',', '.');
                } else {
                    value = value.replace(',', '');
                }
            } else if (separatorComa != -1) { //eslint-disable-line eqeqeq
                value = value.replace(',', '.');
            }

            return parseFloat(value);
        };
    };
});
