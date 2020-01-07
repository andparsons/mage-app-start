/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mage.quickOrderAddToCart', {

        options: {
            buttonAddToCart: 'button.tocart'
        },

        /**
         * Initialization of widget.
         *
         * @private
         */
        _init: function () {
            this.element.on('submit', function () {
                if ($(this.element).valid() === false) {
                    $(this.options.buttonAddToCart).prop('disabled', true);

                    return false;
                }

                $('body').trigger('processStart');
            }.bind(this));
        }
    });

    return $.mage.addToCart;
});
