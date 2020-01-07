/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent'
], function (UiComponent) {
    'use strict';

    return UiComponent.extend({
        defaults: {
            modules: {
                priceStorage: '${ $.priceStorage }'
            },
            listens: {
                '${ $.stepWizardPricingName }:close-modal': 'onEditComplete',
                '${ $.stepWizardName }:update-price': 'onEditComplete',
                '${ $.massactionName }:update-price': 'onEditComplete',
                '${ $.tierPriceFormName }:render-form': 'onEditComplete'
            }
        },

        /**
         * On grid edit complete.
         *
         * @param {Function|Undefined} callback
         * @private
         */
        onEditComplete: function (callback) {
            this.priceStorage().saveProductsCustomPrice().then(callback);
        }
    });
});
