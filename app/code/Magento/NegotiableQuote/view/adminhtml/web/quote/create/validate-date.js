/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/validate'
], function ($) {
    'use strict';

    $.widget('mage.validateDate', {
        options: {
            compareDate: '[data-role="compare-date"]',
            expirationDate: '[data-role="expiration-date"]',
            oldDate: ''
        },

        /**
         * Build widget
         * @private
         */
        _create: function () {
            this.options.oldDate = $(this.options.expirationDate).val();
            this._bind();
        },

        /**
         * Set events
         * @private
         */
        _bind: function () {
            $(this.options.expirationDate).on('change', $.proxy(this._validateDate, this));
        },

        /**
         * Add date validation events
         * @private
         */
        _validateDate: function () {
            var comNumb = new Date,
                expVal = $(this.options.expirationDate).val(),
                expDate = $(this.options.expirationDate).datepicker('getDate');

            if (expVal !== '' && comNumb > expDate && !this._checkToday(expDate, comNumb) ||
                expVal !== '' && !this.isValidDateFormat(expVal)
            ) {
                $(this.options.expirationDate).val(this.options.oldDate);
            } else if (expVal !== '') {
                $(this.options.expirationDate).datepicker('setDate', expDate);
                this.options.oldDate = $(this.options.expirationDate).val();
            }
        },

        /**
         * Is date format valid.
         *
         * @param {String} date
         * @returns {Boolean}
         */
        isValidDateFormat: function (date) {
            var isValid, dateFormat = $(this.options.expirationDate).datepicker('option', 'dateFormat');

            try {
                $.datepicker.parseDate(dateFormat, date);
                isValid = true;
            } catch (e) {
                isValid = false;
            }

            return isValid;
        },

        /**
         * @param {Date}fDate
         * @param {Date}sDate
         * @return {Boolean}
         * @private
         */
        _checkToday: function (fDate, sDate) {
            if (fDate.getDay() === sDate.getDay() &&
                fDate.getMonth() === sDate.getMonth() &&
                fDate.getYear() === sDate.getYear()
            ) {
                return true;
            }
        }
    });

    return $.mage.validateDate;
});
