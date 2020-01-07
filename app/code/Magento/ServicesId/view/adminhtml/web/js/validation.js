/**
 * ServicesId client side validation rules
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require([
    'jquery',
    'mage/translate',
    'mage/validation'
], function ($) {
    'use strict';

    $.validator.addMethod('validate-uuid', function (v) {
        return /[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$/i.test(v);
    }, $.mage.__('Please enter a valid Instance ID.'));
});
