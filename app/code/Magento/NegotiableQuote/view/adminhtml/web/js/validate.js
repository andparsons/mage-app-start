/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/validate'
], function ($) {
    'use strict';

    var rules = {
        'alpha-numeric-space': [
            function (value, element) {
                return this.optional(element) || /^[a-zA-Z\(,\?\)\+\( \?\)\+]+$/i.test(value);
            },
            'Only alpha-numeric characters and commas are allowed in this field.'
        ]
    };

    $.each(rules, function (i, rule) {
        rule.unshift(i);
        $.validator.addMethod.apply($.validator, rule);
    });

    return function (target) {
        return target;
    };
});
