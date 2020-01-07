/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global Class, $ */
define(['Magento_NegotiableQuote/catalog/product/composite/configure'], function () {
    'use strict';

    var DateOption;

    /**
     * Validation callback
     *
     * @param {String} elmId
     * @param {String} result
     *
     * @private
     */
    window.validateOptionsCallback = function (elmId, result) {
        var container = $(elmId).up('ul.options-list');

        if (!container) {
            return;
        }

        if (result == 'failed') { //eslint-disable-line eqeqeq
            container.removeClassName('validation-passed');
            container.addClassName('validation-failed');
        } else {
            container.removeClassName('validation-failed');
            container.addClassName('validation-passed');
        }
    };

    window.productConfigure.opConfig = {};

    DateOption = Class.create({

        /**
         * Get day in target month
         *
         * @param {Number} month
         * @param {Number} year
         *
         * @return {Number} date
         *
         * @private
         */
        getDaysInMonth: function (month, year) {
            var curDate = new Date();

            if (!month) {
                month = curDate.getMonth();
            }

            // leap year assumption for unknown year
            if (month == 2 && !year) { //eslint-disable-line eqeqeq
                return 29;
            }

            if (!year) {
                year = curDate.getFullYear();
            }

            return 32 - new Date(year, month - 1, 32).getDate();
        },

        /**
         * Reload month in param
         *
         * @param {Object} event
         *
         * @private
         */
        reloadMonth: function (event) {
            var selectEl = event.findElement(),
                idParts = selectEl.id.split('_'),
                optionIdPrefix, month, year, dayEl, days, i, lastDay;

            if (idParts.length !== 3) {
                return false;
            }
            optionIdPrefix = idParts[0] + '_' + idParts[1];
            month = parseInt($(optionIdPrefix + '_month').value); //eslint-disable-line radix
            year = parseInt($(optionIdPrefix + '_year').value); //eslint-disable-line radix
            dayEl = $(optionIdPrefix + '_day');
            days = this.getDaysInMonth(month, year);

            //remove days
            for (i = dayEl.options.length - 1; i >= 0; i--) {
                if (dayEl.options[i].value > days) {
                    dayEl.remove(dayEl.options[i].index);
                }
            }

            // add days
            lastDay = parseInt(dayEl.options[dayEl.options.length - 1].value); //eslint-disable-line radix

            for (i = lastDay + 1; i <= days; i++) {
                this.addOption(dayEl, i, i);
            }
        },

        /**
         * Add options in select with data
         *
         * @param {Object} select
         * @param {String} text
         * @param {Number} value
         *
         * @private
         */
        addOption: function (select, text, value) {
            var option = new Element('option');

            option.value = value;
            option.text = text;

            if (select.options.add) {
                select.options.add(option);
            } else {
                select.appendChild(option);
            }
        }
    });

    window.productConfigure.opConfig.dateOption = new DateOption();
});
