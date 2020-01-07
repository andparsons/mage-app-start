/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/validation',
    'mage/translate'
], function ($) {
    'use strict';

    return function (param) {
        /**
         * Add validation for field on max length characters
         */
        if (param.maxLength) {
            $.validator.addMethod('giftcard-message-max-length', function (v) {
                return v.length <= param.maxLength;
            }, $.mage.__('Maximum length of the message is ' + param.maxLength + ' characters.'));
        }
    };
});
