/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'Magento_Catalog/js/price-utils',
        'mage/validation',
        'mage/translate'
    ],
    function (Component, $, quote, customerData, priceUtils) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_CompanyCredit/payment/companycredit-form',
                purchaseOrderNumber: ''
            },

            /** @inheritdoc */
            setConfig: function () {
                this.config = window.checkoutConfig;
            },

            /** @inheritdoc */
            getAvailableCredit: function () {
                return this.config.payment.companycredit.limitFormatted;
            },

            /** @inheritdoc */
            getExceedLimitMessage: function () {
                return $.mage.__('The credit limit for %s is %s. It will be exceeded by %s with this order.')
                    .replace('%s', this.getCompanyName())
                    .replace('%s', this.getLimitFormatted())
                    .replace('%s', this.getExceedLimitAmount());
            },

            /** @inheritdoc */
            getCurrencyQuote: function () {
                return this.config.quoteData['quote_currency_code'];
            },

            /** @inheritdoc */
            getCurrencyCredit: function () {
                return this.config.payment.companycredit.currency;
            },

            /** @inheritdoc */
            areCurrenciesDifferent: function () {
                return this.config.quoteData['quote_currency_code'] != this.config.payment.companycredit.currency;//eslint-disable-line
            },

            /** @inheritdoc */
            getRate: function () {
                return this.config.payment.companycredit.rate ?
                    parseFloat(this.config.payment.companycredit.rate).toFixed(4) : '';
            },

            /** @inheritdoc */
            isBaseCreditCurrencyRateEnabled: function () {
                return this.config.payment.companycredit.isBaseCreditCurrencyRateEnabled;
            },

            /** @inheritdoc */
            getCompanyName: function () {
                return this.config.payment.companycredit.companyName;
            },

            /** @inheritdoc */
            getLimitFormatted: function () {
                return this.config.payment.companycredit.limitFormatted;
            },

            /** @inheritdoc */
            isCanPlace: function () {
                var credit = this.config.payment.companycredit,
                    totals = quote.getTotals();

                return this.config.payment.companycredit.exceedLimit ||
                    credit.limit > totals()['grand_total'] * credit.rate;
            },

            /** @inheritdoc */
            isExceedLimit: function () {
                var credit = this.config.payment.companycredit,
                    totals = quote.getTotals();

                return this.config.payment.companycredit.exceedLimit &&
                    credit.limit < totals()['grand_total'] * credit.rate;
            },

            /** @inheritdoc */
            getExceedLimitAmount: function () {
                return this.config.payment.companycredit.exceededAmountFormatted;
            },

            /** @inheritdoc */
            getQuoteTotal: function () {
                var credit = this.config.payment.companycredit,
                    baseGrandTotal = quote.getTotals()()['base_grand_total'],
                    priceFormat;

                if (baseGrandTotal && credit.baseRate) {
                    priceFormat = $.extend({}, quote.getPriceFormat());
                    priceFormat.pattern = this.config.payment.companycredit.priceFormatPattern;

                    return priceUtils.formatPrice(baseGrandTotal * credit.baseRate, priceFormat);
                }

                return $.mage.__('unavailable.');
            },

            /** @inheritdoc */
            initObservable: function () {
                this._super()
                    .observe('purchaseOrderNumber');

                return this;
            },

            /** @inheritdoc */
            getData: function () {
                return {
                    method: this.item.method,
                    'po_number': this.purchaseOrderNumber(),
                    'additional_data': null
                };

            },

            /** @inheritdoc */
            validate: function () {
                var $form = $('form[data-role=purchaseorder-form]');

                $form.validation();

                return $form.valid();
            }
        });
    }
);
