/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/abstract',
    'mage/translate'
], function (ko, $, _, Abstract, $t) {
    'use strict';

    var pattern = /^\d*\.?\d+$/,
        ERRORS = {
            required: $t('This is a required field.'),
            invalid: $t('Please enter a valid number in this field.')
        };

    return Abstract.extend({
        defaults: {
            actionSelections: null,
            currentCurrency: ''
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Component} Chainable.
         */
        initObservable: function () {
            this._super().observe(['rates', 'actionSelections']);

            return this;
        },

        /**
         * Check of update currency field.
         *
         * @param {String} code - Currency code.
         */
        getConversionRates: function (code) {
            var data;

            if (!code) {
                return;
            }

            data = {
                'currency_to': code
            };
            data = _.extend(data, this.actionSelections());
            this.currentCurrency = code;

            $.ajax({
                url: this.getConversionRatesUrl,
                type: 'get',
                data: data,
                dataType: 'json',
                context: this,
                showLoader: true
            }).done(function (localData) {
                if (localData.status !== 'success') {
                    return;
                }
                this.onGetConversionRates(localData);
            });
        },

        /**
         * Handler function on 'getConversionRates' action.
         *
         * @param {Object} data - Server response.
         */
        onGetConversionRates: function (data) {
            var options = Object.keys(data['currency_rates']).map(function (currency) {
                var ratesObj = data['currency_rates'][currency];

                return {
                    oldCur: currency,
                    newCur: this.currentCurrency,
                    rate: this._formatRate(ratesObj),
                    error: ko.observable('')
                };
            }, this);

            this.rates(options);
        },

        /**
         * Change output format for rate.
         *
         * @param {Object} ratesObj - Rate object.
         * @returns {String} Updated rate.
         * @private
         */
        _formatRate: function (ratesObj) {
            var rate = _.first(_.values(ratesObj));

            return rate ? Number(rate).toFixed(4) : '';
        },

        /**
         * Create the label text.
         *
         * @param {Object} rate - Current rate.
         * @returns {String} Field label.
         */
        makeLabel: function (rate) {
            return $t('{oldCur}/{newCur} Rate')
                .replace('{oldCur}', rate.oldCur)
                .replace('{newCur}', rate.newCur);
        },

        /**
         * Validate UI component.
         *
         * @returns {Component} Chainable.
         */
        validate: function () {
            this.valid = true;
            this.rates().forEach(function (elem) {
                if (!elem.rate.trim()) {
                    elem.error(ERRORS.required);
                    this.valid = false;
                } else if (!pattern.test(elem.rate)) {
                    elem.error(ERRORS.invalid);
                    this.valid = false;
                } else {
                    elem.error('');
                }
            }, this);

            return this;
        },

        /**
         * Get updated rates fields values.
         *
         * @returns {Object} Updated rates.
         */
        getUpdatedRates: function () {
            var currencyRates = {};

            this.rates().forEach(function (elem) {
                currencyRates[elem.oldCur] = elem.rate;
            });

            return currencyRates;
        }
    });
});
