/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/element/abstract'
], function ($, ko, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            websiteFieldName: 'website_id',
            currencySymbolName: 'symbol',
            websiteField: '${ $.provider }:${ $.parentScope }.website_id',
            listens: {
                '${ $.websiteField }': 'setCurrencyCode'
            },
            imports: {
                parent: '${ $.provider }:${ $.parentScope }'
            }
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
         * Sets initial value of the element and subscribes to it's changes.
         *
         * @returns {Abstract} Chainable.
         */
        setInitialValue: function () {
            this._super()
                .setCurrencyCode(this.parent[this.websiteFieldName]);

            return this;
        },

        /**
         * Set new label for currency field
         *
         * @public
         */
        setCurrencyCode: function (id) {
            var source = typeof this.source === 'function' ? this.source() : this.source;

            source.data['base_currencies'].forEach(function (el) {
                if (el[this.websiteFieldName] == id) { //eslint-disable-line eqeqeq
                    this.set('addbefore', el[this.currencySymbolName]);
                }
            }, this);
        }
    });
});
