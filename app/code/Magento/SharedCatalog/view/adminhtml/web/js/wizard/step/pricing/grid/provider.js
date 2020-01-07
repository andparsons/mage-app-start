/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/provider'
], function (GridProvider) {
    'use strict';

    return GridProvider.extend({
        defaults: {
            modules: {
                priceStorage: '${ $.priceStorage }'
            }
        },

        /** @inheritdoc */
        reload: function (options) {
            return this.saveTempData()
                .then(this._super.bind(this, options));
        },

        /**
         * Save temp data.
         *
         * @returns {jQuery.Promise}
         */
        saveTempData: function () {
            return this.priceStorage().saveProductsCustomPrice();
        }
    });
});
