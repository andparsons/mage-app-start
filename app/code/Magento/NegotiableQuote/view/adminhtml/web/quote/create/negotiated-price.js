/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define
([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'mage/template',
    'text!Magento_NegotiableQuote/template/error-validation.html',
    'jquery/ui'
], function ($, utils, mageTemplate, errorTpl) {
    'use strict';

    $.widget('mage.negotiatedPrice', {

        options: {
            inputs: {
                percentageDiscount: '[name="quote[negotiated_price_value][1]"]',
                amountDiscount: '[name="quote[negotiated_price_value][2]"]',
                proposedGrandTotal: '[name="quote[negotiated_price_value][3]"]',
                negotiatedPrice: '[name*="quote[negotiated_price_value]"]'
            },
            catalogPriceValue: 0,
            priceTemplate: '<span class="price"><%- data %></span>',
            priceFormat: '',
            isError: false,
            errorMessage: '',
            conditionError: 'higher'
        },

        /**
         * Build widget
         *
         * @private
         */
        _create: function () {
            var element = this.element,
                self = this,
                rows = element.find('tr');

            this.errorBlockTmpl = mageTemplate(errorTpl);
            this._bind();

            rows.each(function () {
                var $row = $(this),
                    $radio = $row.find('input[type="radio"]'),
                    $input = $row.find('input[type="number"]');

                $input.prop('disabled', !$radio.prop('checked') || $radio.prop('disabled'));
                $input.on('keyup', self.onNegotiatedPriceEdit.bind(self, $input));
                $input.on('focusout', self.formatPriceValue.bind(self, $input));

                $radio.on('change', function () {
                    $input.prop('disabled', false);
                    element.find('input[type="number"]').not($input).prop('disabled', true).removeClass('hasError');

                    if ($input.val()) {
                        if (self._validateField($input)) {
                            $input.addClass('hasError');
                            self.showMessage(self);
                        }
                    }
                });

                if ($radio.prop('checked') && $input.val()) {
                    $input.trigger($.Event('keyup'));
                }
            });
        },

        /**
         * Add events on items.
         *
         * @private
         */
        _bind: function () {
            var handlers = {};

            handlers.setCatalogPrice = '_setCatalogPrice';
            this._on(handlers);
        },

        /**
         * Callback Handler from edit price field
         *
         * @param {Object} $input
         * @public
         */
        onNegotiatedPriceEdit: function ($input) {
            this.options.isError = this._validateField($input);

            if (this.options.isError) {
                $input.addClass('hasError');
                this.showMessage(this);
            } else {
                this.calculateNegotiatedPrice($input);
                $input.removeClass('hasError');
                this.hideMessage(this);
            }
        },

        /**
         * Changes value type and displays it as a number with decimals
         *
         * @param {Object} $input
         */
        formatPriceValue: function ($input) {
            var priceValue = $input.val();

            if (priceValue) {
                $input.val((+priceValue).toFixed(2));
            }
        },

        /**
         * Calculate and set value in fields
         *
         * @param {Object} $input
         * @public
         */
        calculateNegotiatedPrice: function ($input) {
            var fieldValue = $input.val(),
                amountDiscount = 0,
                percentageDiscount = 0;

            /* eslint-disable max-depth */
            if (fieldValue) {
                switch ($input.attr('data-key')) {
                    case 'percentage':
                        amountDiscount = this.getAmountValue(fieldValue, this.options.catalogPriceValue);
                        $(this.options.inputs.amountDiscount).val(amountDiscount);
                        $(this.options.inputs.proposedGrandTotal).val((this.options.catalogPriceValue - amountDiscount)
                            .toFixed(2));
                        break;

                    case 'amount':
                        percentageDiscount = this.getPercentageValue(fieldValue, this.options.catalogPriceValue);

                        if (!isNaN(percentageDiscount)) {
                            $(this.options.inputs.percentageDiscount).val(percentageDiscount);
                            $(this.options.inputs.proposedGrandTotal).val((this.options.catalogPriceValue - fieldValue)
                                .toFixed(2));
                        }
                        break;

                    case 'proposed':
                        amountDiscount = this.options.catalogPriceValue - fieldValue;
                        percentageDiscount = this.getPercentageValue(amountDiscount, this.options.catalogPriceValue);

                        if (!isNaN(percentageDiscount)) {
                            $(this.options.inputs.percentageDiscount).val(percentageDiscount);
                            $(this.options.inputs.amountDiscount).val(amountDiscount.toFixed(2));
                        }
                        break;
                    default:
                        return;
                }
            } else {
                $(this.options.inputs.percentageDiscount).val('');
                $(this.options.inputs.amountDiscount).val('');
                $(this.options.inputs.proposedGrandTotal).val('');
            }

            /* eslint-enable max-depth */
        },

        /**
         * Show error message
         *
         * @public
         */
        showMessage: function () {
            var addedBlock,
                data = {};

            if ($(this.element).find('.error-message').length === 0) {
                if (this.options.conditionError == 'less') { //eslint-disable-line eqeqeq
                    data.text = this.options.errorMessage.less;
                } else {
                    data.text = this.options.errorMessage.higher;
                }

                data.oneRow = true;

                addedBlock = $(this.errorBlockTmpl({
                    data: data
                }));

                $(this.element)
                    .find('tr:last')
                    .after(addedBlock);
            }
        },

        /**
         * Hide error message
         *
         * @public
         */
        hideMessage: function () {
            $(this.element).find('.error-message').remove();
        },

        /**
         * Validate field
         *
         * @param {Object} $input
         * @returns {Boolean}
         * @private
         */
        _validateField: function ($input) {
            var fieldValue = $input.val();

            switch ($input.attr('data-key')) {
                case 'percentage':
                    return this._checkCharacterError(fieldValue, 100);

                case 'amount':
                case 'proposed':
                    return this._checkCharacterError(fieldValue, this.options.catalogPriceValue);
                default:
                    return false;
            }
        },

        /**
         * Set type of error.
         *
         * @param {Number} val
         * @private
         */
        _setTypeError: function (val) {
            if (val < 0) {
                this.options.conditionError = 'less';
            } else {
                this.options.conditionError = 'higher';
            }
        },

        /**
         * Check character error for field
         *
         * @param {String} val
         * @param {Number} condition
         * @returns {Boolean}
         * @private
         */
        _checkCharacterError: function (val, condition) {
            var fieldVal = parseFloat(val);

            if (fieldVal) {
                this._setTypeError(fieldVal);
            }

            return fieldVal < 0 || fieldVal > condition;
        },

        /**
         * Set price in catalog
         *
         * @param {Object} e
         * @param {Number} val
         * @private
         */
        _setCatalogPrice: function (e, val) {
            this.options.catalogPriceValue = val;

            if ($(this.options.inputs.negotiatedPrice + ':not(:disabled)').length > 0) {
                this.calculateNegotiatedPrice($(this.options.inputs.negotiatedPrice + ':not(:disabled)'));
            }
        },

        /**
         * Get percentage
         *
         * @param {Number} num
         * @param {Number} total
         * @public
         */
        getPercentageValue: function (num, total) {
            return (num * 100 / total).toFixed(2);
        },

        /**
         * Get amount
         *
         * @param {Number} num
         * @param {Number} total
         * @public
         */
        getAmountValue: function (num, total) {
            return (total * num / 100).toFixed(2);
        }
    });

    return $.mage.negotiatedPrice;
});
