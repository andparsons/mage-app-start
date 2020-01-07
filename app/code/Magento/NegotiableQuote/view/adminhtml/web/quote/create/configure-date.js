/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global dateOption*/
define([
    'jquery',
    'Magento_NegotiableQuote/quote/create/init-date',
    'mage/backend/validation',
    'Magento_NegotiableQuote/catalog/product/composite/configure',
    'mage/translate'
], function ($) {
    'use strict';

    $.widget('mage.configureDate', {
        options: {
            condition: true,
            id: 0,
            require: '',
            monthOptions: '',
            yearOptions: ''
        },

        /**
         * Build widget.
         *
         * @private
         */
        _create: function () {
            this._setDateOptions();
            this._bind();
            this._addValid();
        },

        /**
         * Set events.
         *
         * @private
         */
        _bind: function () {
            if (!this.options.condition) {
                $(this.options.monthOptions).on('change', this._reloadMonth);
                $(this.options.yearOptions).on('change', this._reloadMonth);
            }
        },

        /**
         * Set options for date in product configure.
         *
         * @private
         */
        _setDateOptions: function () {
            window.dateOption = window.productConfigure.opConfig.dateOption;
        },

        /**
         * Reload param month in configure.
         *
         * @private
         */
        _reloadMonth: function () {
            window.dateOption.reloadMonth.bind(dateOption);
        },

        /**
         * Add validation method.
         *
         * @private
         */
        _addValid: function () {
            var self = this;

            if (this.options.require) {
                $.validator.addMethod('validate-datetime-' + this.options.id, function () {
                    var dateTimeParts = $('.datetime-picker[id^="options_' + self.options.id + '"]'),
                        i;

                    for (i = 0; i < dateTimeParts.length; i++) {
                        if (dateTimeParts[i].value == '') { //eslint-disable-line eqeqeq
                            return false;
                        }
                    }

                    return true;
                },  $.mage.__('This is a required option.'));
            } else {
                $.validator.addMethod('validate-datetime-' + this.options.id, function () {
                    var dateTimeParts = $('.datetime-picker[id^="options_' + self.options.id + '"]'),
                        hasWithValue = false,
                        hasWithNoValue = false,
                        pattern = /day_part$/i,
                        i;

                    for (i = 0; i < dateTimeParts.length; i++) {
                        if (!pattern.test(dateTimeParts[i].id)) {
                            if (dateTimeParts[i].value === '') { //eslint-disable-line max-depth
                                hasWithValue = true;
                            } else {
                                hasWithNoValue = true;
                            }
                        }
                    }

                    return hasWithValue ^ hasWithNoValue;
                }, $.mage.__('The field isn\'t complete.'));
            }
        }
    });

    return $.mage.configureDate;
});
