/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/template',
    'text!Magento_NegotiableQuote/template/price.html',
    'jquery/validate'
], function ($, mageTemplate, priceTpl) {
    'use strict';

    $.widget('mage.negotiationTotals', {
        options: {
            price: '',
            subtotal: '.subtotal td',
            subtotalTax: '.subtotalTax td',
            discount: '.discount td',
            tax: '.tax td',
            cost: '.cost td',
            catalogPrice: '.catalog_price td',
            quoteTax: '.quote_tax td',
            proposedQuotePrice: '.proposed_quote_price td',
            grandTotal: '.grand_total td',
            changeParam: 'discountOrigin',
            shippingPrice: '[data-role="shipping-price-wrap"]',
            switchBlock: '.discount',
            elements: {},
            catalogPriceWrap: '[data-role="catalog-price"]'
        },

        /**
         * Build widget
         * @private
         */
        _create: function () {
            this._bind();
            this.options.price = mageTemplate(priceTpl);
            this._setElements();
        },

        /**
         * Set events
         * @private
         */
        _bind: function () {
            this.element.on('updateTotalTable', $.proxy(this._updateData, this));
        },

        /**
         * @param {jQuery.Event} e
         * @param {Array} data
         * @private
         */
        _updateData: function (e, data) {
            var i;

            if (data) {
                for (i in this.options.elements) { //eslint-disable-line guard-for-in
                    this.addPrice(data[i], this.options.elements[i]);
                }

                this._setVisibleField(data[this.options.changeParam]);
            }
        },

        /**
         * @param {Number} val
         * @private
         */
        _setVisibleField: function (val) {
            var tableElement = $(this.options.catalogPriceWrap),
                field = tableElement.find(this.options.switchBlock);

            field.toggleClass('hidden', val === 0);
        },

        /**
         * @param {*} data
         * @param {jQuery} el
         */
        addPrice: function (data, el) {
            var errorBlock;

            if (typeof data !== 'string') {
                data.isObject = true;
            }

            errorBlock = $(this.options.price({
                data: data
            }));

            el.html('');
            el.append(errorBlock);
        },

        /**
         * @private
         */
        _setElements: function () {
            this.options.elements = {
                subtotal: $(this.options.subtotal),
                subtotalTax: $(this.options.subtotalTax),
                discount: $(this.options.discount),
                tax: $(this.options.tax),
                cost: $(this.options.cost),
                catalogPrice: $(this.options.catalogPrice),
                quoteTax: $(this.options.quoteTax),
                quoteSubtotal: $(this.options.proposedQuotePrice),
                shippingPrice: $(this.options.shippingPrice),
                grandTotal: $(this.options.grandTotal).find('strong')
            };
        }
    });

    return $.mage.negotiationTotals;
});
