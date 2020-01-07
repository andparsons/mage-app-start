/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'Magento_Ui/js/form/element/abstract'
], function (ko, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            currencyLabel: ko.observable()
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {Abstract} Chainable.
         */
        initObservable: function () {
            this._super();
            this.observe('addbefore');

            return this;
        },

        /**
         * Set new label for currency field
         *
         * @public
         */
        setCurrencyLabel: function (currency) {
            this.currencyLabel(currency);
        }
    });
});
