/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    $.widget('mage.shippingMethod', {

        options: {
            shippingMethodInfo: '#quote-shipping-method-info',
            quoteShippingMethodChoose: '[data-role="quote-shipping-method-choose"]'
        },

        /**
         * Build widget
         * @private
         */
        _create: function () {
            this._bind();
        },

        /**
         * Bind listeners on shipping elements
         * @private
         */
        _bind: function () {
            $(this.element).on('click', $.proxy(this._showShipping, this));
        },

        /**
         * @private
         */
        _showShipping: function (e) {
            e.preventDefault();
            $(this.options.shippingMethodInfo).hide();
            $(this.options.quoteShippingMethodChoose).show();
        }
    });

    return $.mage.shippingMethod;
});
