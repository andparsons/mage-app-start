/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/lib/validation/validator',
    'Magento_Ui/js/lib/validation/utils',
    'mage/translate'
], function ($, validator, utils) {
    'use strict';

    return function (target) {
        validator.addRule(
            'validate-purchase-order-number',
            function (value) {
                return !value || /^[a-zA-Z0-9-/]+$/.test(value);
            },
            $.mage.__('Please use only letters (a-z or A-Z), numbers (0-9), dash (-) or slash (/) in this field.')
        );

        validator.addRule(
            'validate-currency-rate',
            function (value) {
                var numValue = utils.parseNumber(value);

                return !utils.isEmpty(value) && (!isNaN(numValue) && numValue > 0);
            },
            $.mage.__('Please enter a valid number in this field.')
        );

        return target;
    };
});
