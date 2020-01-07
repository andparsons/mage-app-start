/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    $.widget('mage.shippingMethodLoad', {
        options: {
            url: '',
            isAjax: false,
            actionAnchor: '[data-action="get-shipping"]',
            shippingMethod: '[name="quote[shipping_method]"]',
            shippingPrice: '[data-role="shipping-price"]',
            formPrice: '[data-role="quote-shipping-price-form"]'
        },

        /**
         * Build widget
         *
         * @private
         */
        _create: function () {
            this.link = $(this.element).find(this.options.actionAnchor);
            this._bind();
        },

        /**
         * Bind listeners on shipping elements
         *
         * @private
         */
        _bind: function () {
            this._on(this.link, {
                'click': this._ajaxSend
            });
            this._on(this.element, {
                'change': this._update
            });
        },

        /**
         * Render element
         *
         * @private
         */
        _render: function (el) {
            this.element
                .empty()
                .append(el)
                .trigger('contentUpdated');
        },

        /**
         * Enable shipping price field
         *
         * @private
         */
        _enableField: function () {
            $(this.options.shippingPrice).prop('disabled', false);
        },

        /**
         * Get checked shipping method
         *
         * @return {String|Boolean} method name or null
         * @private
         */
        _getCheckedMethod: function () {
            var checkedMethod = $(this.options.shippingMethod + ':checked');

            return checkedMethod.length ? checkedMethod.val() : null;
        },

        /**
         * Update shipping price
         *
         * @private
         */
        _update: function () {
            this._enableField();

            if ($(this.options.formPrice).valid()) {
                this.element.trigger('updateShipping', {
                    method: this._getCheckedMethod(),
                    price: $(this.options.shippingPrice).val() || null
                });
            }
        },

        /**
         * Send ajax request
         *
         * @private
         */
        _ajaxSend: function (e) {
            e.preventDefault();

            if (!this.options.isAjax && this.options.url !== '') {
                this.options.isAjax = true;

                $.ajax({
                    url: this.options.url,
                    type: 'get',
                    data: {
                        'custom_shipping_price': $(this.options.shippingPrice).val()
                    },
                    showLoader: true,
                    success: $.proxy(function (res) {
                        this._render(res);

                        if (this._getCheckedMethod()) {
                            this._enableField();
                        }
                    }, this),
                    error: $.proxy(function () {
                        console.log('error ajax');
                    }, this)
                });
            }
        }
    });

    return $.mage.shippingMethodLoad;
});
