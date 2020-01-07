/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal-component',
    'Magento_Ui/js/lib/validation/utils',
    'mage/validation',
    'mage/translate'
], function ($, Element, utils) {
    'use strict';

    return Element.extend({
        defaults: {
            isAjax: false,
            url: null,
            onCancel: 'resetCurrencyCode',
            modules: {
                modalHtmlContent: '${ $.modalHtmlContent }',
                creditLimit: '${ $.creditLimit }',
                currencyCode: '${ $.currencyCode }',
                currencyRate: '${ $.currencyRate }'
            },
            listens: {
                '${ $.currencyCode }:value': 'checkCurrencyCode'
            }
        },

        /**
         * Init ui component
         *
         * @returns {Element}
         */
        initialize: function () {
            this.currencyMsgArray = $.mage.__('The Company credit will be recalculated using the %s' +
                ' conversion rate specified below. This operation cannot be undone.')
                .split('%s');

            return this._super();
        },

        /**
         * Check currency code value and open modal if changed
         *
         * @param {String} data
         * @public
         */
        checkCurrencyCode: function (data) {
            if (this.prevCurrencyCodeName !== data) {
                this.openModal(data);
            }
        },

        /**
         * Open modal
         *
         * @param {String} data
         * @public
         */
        openModal: function (data) {
            this.initialCurrencyCodeName = this.currencyCode().initialValue;
            this.newCurrencyCodeName = data;
            this.getInitialCurrencySymbol();

            if (this.initialCurrencyCodeName !== this.newCurrencyCodeName) {
                this.getCurrencyRate().setCurrencyLabel(this.initialCurrencyCodeName + '/' + this.newCurrencyCodeName);
                this.oldCreditLimit = this.source.get('data.company_credit.credit_limit') || 0;

                this.modalHtmlContent().updateContent(this.currencyMsgArray.join('<strong>' +
                    this.initialCurrencyCodeName +
                    '/' +
                    this.newCurrencyCodeName +
                    '</strong>'));

                this._resetFields()
                    ._setFields();
                this._super();
            } else {
                this.resetCreditLimit();
            }
        },

        /**
         * Close modal
         *
         * @public
         */
        closeModal: function () {
            this.currencyRate().disable();

            this._super();
        },

        /**
         * Get currency rate
         *
         * @returns {Object}
         * @public
         */
        getCurrencyRate: function () {
            if (!this.currencyRateUi) {
                this.currencyRateUi = this.currencyRate();
            }

            return this.currencyRateUi;
        },

        /**
         * Get currency symbol
         *
         * @returns {String}
         * @public
         */
        getInitialCurrencySymbol: function () {
            if (!this.oldCurrencySymbol) {
                this.oldCurrencySymbol = this.creditLimit().addbefore();
            }

            return this.oldCurrencySymbol;
        },

        /**
         * Set data to fields of modal
         *
         * @returns {Object} this
         * @private
         */
        _setFields: function () {
            var sendData = {
                'currency_from': this.initialCurrencyCodeName,
                'currency_to': this.newCurrencyCodeName
            };

            if (!this.isAjax) {
                this.isAjax = true;

                $.ajax({
                    url: this.url,
                    data: sendData,
                    type: 'post',
                    dataType: 'json',
                    showLoader: true,

                    /**
                     * @callback
                     */
                    success: $.proxy(function (res) {
                        if (res && res['currency_rate']) {
                            this.source.set('data.credit_limit_change.currency_rate', res['currency_rate']);
                            this.currencySymbol = res['currency_symbol'];
                        }

                        this.isAjax = false;
                    }, this)
                });
            }

            return this;
        },

        /**
         * Reset and clear fields of modal
         *
         * @returns {Object} this
         * @private
         */
        _resetFields: function () {
            this.clear();
            this.currencyRate().enable().reset();

            return this;
        },

        /**
         * Validate everything validatable in modal
         *
         * @param {Object} elem
         * @public
         */
        validate: function (elem) {
            if (elem) {
                this._super();
            }
        },

        /**
         * Set new credit limit
         *
         * @public
         */
        setCreditLimit: function () {
            var newCreditLimit,
                newCurrencyRate = utils.parseNumber(this.source.get('data.credit_limit_change.currency_rate'));

            this.valid = true;
            this.elems().forEach(this.validate, this);

            if (this.valid) {
                newCreditLimit = utils.parseNumber(this.oldCreditLimit) * newCurrencyRate;
                this.source.set('data.company_credit.currency_rate', newCurrencyRate);
                this.source.set('data.company_credit.credit_limit', newCreditLimit.toFixed(2));
                this.prevCurrencyCodeName = this.newCurrencyCodeName;
                this.creditLimit().addbefore(this.currencySymbol);
                this.closeModal();
            }
        },

        /**
         * Reset credit limit
         *
         * @public
         */
        resetCreditLimit: function () {
            this.source.set('data.company_credit.currency_rate', 1);
            this.source.set('data.company_credit.credit_limit', this.creditLimit().initialValue);
            this.creditLimit().addbefore(this.oldCurrencySymbol);
            this.prevCurrencyCodeName = this.initialCurrencyCodeName;
        },

        /**
         * Reset currency code
         *
         * @public
         */
        resetCurrencyCode: function () {
            if (!this.prevCurrencyCodeName) {
                this.prevCurrencyCodeName = this.initialCurrencyCodeName;
            }

            this.source.set('data.company_credit.currency_code', this.prevCurrencyCodeName);
            this.closeModal();
        }
    });
});
