/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/template',
    'text!Magento_NegotiableQuote/template/error.html',
    'mage/translate'
], function ($, mageTemplate, errorTpl) {
    'use strict';

    $.widget('mage.validateField', {
        options: {
            labelError: '[data-role="error"]',
            errorText: {
                text: '',
                tableError: true

            },
            updateElement: '[data-role="update-quote"]',
            qtyOldValueField: '[data-role="qty-amount-old"]',
            allowSend: {
                allow: true,
                block: false
            }
        },

        /**
         * Build widget
         *
         * @private
         */
        _create: function () {
            this.errorBlockTmpl = mageTemplate(errorTpl);
            this.updateBtn = $(this.options.updateElement);
            this.qtyOldValueField = $(this.options.qtyOldValueField);
            this._bind();
        },

        /**
         * Bind events
         *
         * @private
         */
        _bind: function () {
            this.element.on('keyup', $.proxy(this._validateField, this));
            this.element.on('change', $.proxy(this._enableBtn, this));
        },

        /**
         * Validate field.
         *
         * @param {Object} e
         *
         * @private
         */
        _validateField: function (e) {
            var valEl = $(e.target).val();

            this._clearError($(e.target).parent());
            this.updateBtn.trigger('blockSend', this.options.allowSend.allow);

            if (parseFloat(valEl) <= 0) {
                this._setTextError($(e.target), $.mage.__('Please enter a number greater than 0 in this field'));
            } else if (valEl === '') {
                this._setTextError($(e.target), $.mage.__('This is a required field.'));
            } else if (!this._checkVal(valEl)) {
                this._setTextError($(e.target), $.mage.__('Please enter a non-decimal number please'));
            }
        },

        /**
         * Validate a non-decimal.
         *
         * @param {String} val
         * @returns {Boolean} result of validation
         * @private
         */
        _checkVal: function (val) {
            return /^-?\d+$/.test(val);
        },

        /**
         * Enable button if we get new value
         *
         * @private
         */
        _enableBtn: function () {
            var isEnabled = this.element.val() === this.element.prev(this.qtyOldValueField).val();

            this.updateBtn.toggleClass('enabled', !isEnabled)
                .attr('disabled', isEnabled);
        },

        /**
         * Add text error.
         *
         * @param {Object} el
         * @param {String} text
         *
         * @private
         */
        _setTextError: function (el, text) {
            $(this.options.updateElement).trigger('blockSend', this.options.allowSend.block);
            this.options.errorText.text = text;
            this._renderError(el, this.options.errorText);
        },

        /**
         * Render error.
         *
         * @param {Object} el
         * @param {Object} data
         *
         * @private
         */
        _renderError: function (el, data) {
            var errorBlock = $(this.errorBlockTmpl({
                data: data
            }));

            el.after(errorBlock);
        },

        /**
         * Clear error.
         *
         * @param {Object} el
         *
         * @private
         */
        _clearError: function (el) {
            el.find(this.options.labelError).remove();
        }
    });

    return $.mage.validateField;
});
