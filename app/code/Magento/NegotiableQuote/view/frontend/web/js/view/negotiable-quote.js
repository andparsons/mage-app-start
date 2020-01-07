/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'Magento_Company/js/authorization'
], function (Component, customerData, authorization) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();

            this.config = customerData.get('negotiable_quote');
        },

        /**
         * Is sales all allowed.
         *
         * @returns {Boolean}
         */
        isSalesAllAllowed: function () {
            return authorization.isAllowed('Magento_Sales::all');
        },

        /**
         * Is negotiable quote all allowed.
         *
         * @returns {Boolean}
         */
        isNegotiableQuoteAllAllowed: function () {
            return authorization.isAllowed('Magento_NegotiableQuote::all');
        }
    });
});
