/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mage/translate',
    'Magento_Ui/js/grid/columns/column',
    'Magento_SharedCatalog/js/utils/validator/event_key',
    'Magento_Catalog/js/utils/percentage-price-calculator'
], function (_, $t, Column, EventValidator, percentagePriceCalculator) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Magento_SharedCatalog/grid/cells/custom-price',
            priceValue: '',

            hasTierPricesName: 'has_tier_prices',
            customPriceName: 'custom_price',
            priceTypeName: 'price_type',
            originPriceName: 'origin_price',
            productType: 'type_id',
            enableStateName: 'custom_price_enabled',

            customPriceScope: 'data.items.{0}.${ $.customPriceName }',
            priceTypeScope: 'data.items.{0}.${ $.priceTypeName }',

            modules: {
                priceStorage: '${ $.priceStorage }'
            },

            generalOptions: [{
                text: $t('Fixed'),
                value: 'fixed'
            }, {
                text: $t('Discount'),
                value: 'percent'
            }],
            specialOptions: [{
                text: $t('Discount'),
                value: 'percent'
            }],
            currencyTypes: {
                fixed: '${ $.currencySymbol }',
                percent: '%'
            },
            specialProductTypes: {}
        },

        /**
         * Get record custom price value
         *
         * @param {Object} record
         * @param {String} index
         * @returns {String}
         */
        getValue: function (record, index) {
            return record[index];
        },

        /**
         * On price type changed
         *
         * @param {Object} record
         * @returns {String}
         */
        getCurrencySymbol: function (record) {
            var priceType = record[this.priceTypeName];

            if (priceType === 'fixed') {
                this.currencyTypes[priceType] = this.source().data.websites.currencySymbol;
            }

            return priceType === null ? _.values(this.currencyTypes)[0] : this.currencyTypes[priceType];
        },

        /**
         * Get custom price options for a record
         *
         * @param {Object} record
         * @returns {Array}
         */
        getOptions: function (record) {
            return record[this.productType] in this.specialProductTypes ?
                this.specialOptions : this.generalOptions;
        },

        /**
         * On price changed
         *
         * @param {Object} record
         * @param {Element} input
         */
        onChangePrice: function (record, input) {
            var index = record._rowIndex,
                productId = record[this.indexField],
                priceType = record[this.priceTypeName],
                inputValue = input.value,
                websiteId = this.source().get('data.websites.selected'),
                customPrice;

            if (index > -1) {
                this.setCustomPrice(index, inputValue);
            }

            customPrice = this._preparePrice(productId, inputValue, priceType, websiteId);
            this.priceStorage().setCustomPrice(customPrice);
        },

        /**
         * On price type changed
         *
         * @param {Object} record
         * @param {Element} input
         */
        onChangePriceType: function (record, input) {
            var index = record._rowIndex,
                inputValue = input.value;

            if (index > -1) {
                this.setCustomPrice(index, '');
                this.setPriceType(index, inputValue);
            }
        },

        /**
         * On price input keydown
         *
         * @param {Object} record
         * @param {Element} input
         * @param {Object} e
         * @returns {Boolean} let the default event handler proceed
         */
        onPriceInputKeyDown: function (record, input, e) {
            var priceType = record[this.priceTypeName];

            if (e.key === '%' && priceType === 'fixed') {
                this.recalculatePriceByDiscount(record, input);

                return false;
            }

            return this.isPriceInputEventKeyValid(e);
        },

        /**
         * Is price input event key valid.
         *
         * @param {Object} e
         * @returns {Boolean}
         */
        isPriceInputEventKeyValid: function (e) {
            return EventValidator.isDigits(e);
        },

        /**
         * Recalculate price using discount value.
         *
         * @param {Object} record
         * @param {Element} input
         */
        recalculatePriceByDiscount: function (record, input) {
            var index = record._rowIndex,
                priceValue = record[this.originPriceName],
                inputValue = input.value;

            inputValue = percentagePriceCalculator(priceValue, inputValue + '%');

            if (index > -1) {
                this.setCustomPrice(index, inputValue);
            }
            this.onChangePrice(record, input);
        },

        /**
         * Update price_type in source
         *
         * @param {String|Number} index
         * @param {String} value
         */
        setPriceType: function (index, value) {
            this.source().set(this.priceTypeScope.replace('{0}', index), value);
        },

        /**
         * Update custom_price in source
         *
         * @param {String|Number} index
         * @param {String} value
         */
        setCustomPrice: function (index, value) {
            this.source().set(this.customPriceScope.replace('{0}', index), value);
        },

        /**
         * Prepare price
         *
         * @param {Number} productId
         * @param {float} customPrice
         * @param {String} priceType
         * @param {Number} websiteId
         * @returns {Object}
         * @private
         */
        _preparePrice: function (productId, customPrice, priceType, websiteId) {
            return {
                'product_id': productId,
                'custom_price': customPrice,
                'price_type': priceType,
                'website_id': websiteId
            };
        }
    });
});
