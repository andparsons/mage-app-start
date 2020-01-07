/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'Magento_Ui/js/form/element/textarea'
], function (ko, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            listens: {
                '${ $.creditLimitName }:value': 'enable',
                '${ $.currencyCodeName }:value': 'enable',
                '${ $.exceedLimitName }:value': 'enable'
            }
        },

        /**
         * Init ui component
         *
         * @returns {Element}
         */
        initialize: function () {
            return this._super();
        }
    });
});

