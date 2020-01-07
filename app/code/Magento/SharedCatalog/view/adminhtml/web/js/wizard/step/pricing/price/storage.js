/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'underscore',
    'uiLayout',
    'uiComponent'
], function (ko, $, _, layout, UiComponent) {
    'use strict';

    return UiComponent.extend({
        defaults: {
            customPrices: ko.observableArray(),
            priceSavePromise: null,
            clientConfig: {
                component: 'Magento_Ui/js/grid/editing/client',
                name: '${ $.name }_client',
                requestConfig: {
                    showLoader: true
                }
            },
            modules: {
                client: '${ $.clientConfig.name }'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            _.bindAll(this, 'onSaveDone');

            this._super()
                .initClients();

            return this;
        },

        /**
         * Init editing clients.
         *
         * @returns {Object}
         */
        initClients: function () {
            layout([this.clientConfig]);

            return this;
        },

        /**
         * Save custom price into internal storage.
         *
         * @param {Object} customPrice
         */
        setCustomPrice: function (customPrice) {
            this.customPrices.remove(function (price) {
                return price['product_id'] === customPrice['product_id'];
            });
            this.customPrices.push(customPrice);
        },

        /**
         * Save products custom price
         * @private
         */
        saveProductsCustomPrice: function () {
            if (_.isObject(this.priceSavePromise)) {
                return this.priceSavePromise;
            }

            if (!this.customPrices().length) {
                return $.when(true);
            }

            this.priceSavePromise = this.client()
                .save(this.prepareRequestData(this.customPrices()))
                .done(this.onSaveDone);

            return this.priceSavePromise;
        },

        /**
         * Prepare request data
         *
         * @param {Array} prices
         * @returns {Object}
         * @private
         */
        prepareRequestData: function (prices) {
            return {
                prices: prices
            };
        },

        /**
         * On save product price done callback
         *
         * @private
         */
        onSaveDone: function () {
            this.customPrices([]);
            this.priceSavePromise = null;
        }
    });
});
