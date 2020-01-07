/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_NegotiableQuote/js/quote/actions/toggle-show',
    'jquery/validate',
    'mage/validation',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.submitForm', {
        options: {
            formId: '',
            typeEvent: 'click',
            wrapper: '[data-container="items"]',
            validInput: '[data-role*="product"]',
            qtyInput: '[data-role="product-qty"]',
            hiddenBlock: 'order-additional_area',
            showBlock: '[data-action="show-sku-form"]',
            btnUpdate: '[data-role="update-quote"]',
            formRow: '[data-role="wrap"]',
            btnAddQuote: '[data-role="add-to-quote"]',
            lastRow: '',
            valid: false
        },

        /**
         * This method binds elements found in this widget.
         * @private
         */
        _bind: function () {
            $(this.element).on(this.options.typeEvent, $.proxy(this._submitForm, this));
        },

        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function () {
            if (this._checkId()) {
                this._bind();
            }
            this.element.toggleShow({
                toggleBlockId: this.options.hiddenBlock,
                typeEvent: 'hideForm',
                showBlockId: false,
                hideBlockId: false
            });
        },

        /**
         * This method check id.
         * @private
         */
        _checkId: function () {
            if (!this.options.formId) {
                return false;
            }

            if (typeof this.options.formId !== 'string') {
                return false;
            }

            if (this.options.formId[0] !== '#') {
                this.options.formId = '#' + this.options.formId;

                return this.options.formId;
            }

            return this.options.formId;
        },

        /**
         * @private
         */
        _clearForm: function () {
            $(this.options.formId).find(this.options.formRow).remove();
        },

        /**
         * @private
         */
        _disableBtn: function () {
            $(this.options.btnAddQuote).attr('disabled', 'disabled');
            $(this.options.btnAddQuote).addClass('disabled');
        },

        /**
         * This method check id.
         * @private
         */
        _checkValue: function () {
            var formRows = $(this.options.formId).find(this.options.formRow);

            this.options.lastRow = formRows[formRows.length - 1];
            this.options.lastRow.remove();
        },

        /**
         * This method toggle show block.
         * @private
         */
        _submitForm: function () {
            this._checkValue();

            if ($(this.options.formId).valid()) {
                this.element.trigger('hideForm');
                $(this.options.showBlock).show();
                $(this.options.btnUpdate).trigger('addbysku');
                this._clearForm();
                this._disableBtn();
            }
            $(this.options.wrapper).append(this.options.lastRow);
        }
    });

    return $.mage.submitForm;
});
