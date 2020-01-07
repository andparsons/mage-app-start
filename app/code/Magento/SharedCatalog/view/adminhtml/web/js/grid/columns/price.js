/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiLayout',
    'mage/translate',
    'Magento_Ui/js/grid/columns/column'
], function (_, layout, $t, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Magento_SharedCatalog/grid/cells/price',
            productType: 'type_id',
            priceView: 'price_view',
            specialProductTypes: {}
        },

        /**
         * Initializes column price component.
         *
         * @returns {PriceColumn} Chainable.
         */
        initialize: function () {
            this._super();

            return this;
        },

        /**
         * Get record price value
         *
         * @param {Object} record
         * @param {String} index
         * @returns {String}
         */
        getValue: function (record, index) {
            return record[index];
        },

        /**
         * Get record max price value
         *
         * @param {Object} record
         * @param {String} index
         * @returns {String}
         */
        getMaxValue: function (record, index) {
            return record['max_' + index];
        },

        /**
         * Check if record price view is set
         *
         * @param {Object} record
         * @returns {Boolean}
         */
        hasPriceView: function (record) {
            return record[this.priceView] != 0; //eslint-disable-line eqeqeq
        },

        /**
         * Check if price template exists for a record
         *
         * @param {Object} record
         * @returns {Boolean}
         */
        hasPriceTemplate: function (record) {
            return record[this.productType] in this.specialProductTypes;
        },

        /**
         * Get price template for a record
         *
         * @param {Object} record
         * @returns {String}
         */
        getPriceTemplate: function (record) {
            var productType = record[this.productType];

            return this.specialProductTypes[productType];
        }
    });
});
