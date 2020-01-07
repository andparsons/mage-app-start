/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_RequisitionList/js/requisition/action/abstract',
    'underscore',
    'jquery'
], function (RequisitionComponent, _, $) {
    'use strict';

    return RequisitionComponent.extend({
        /**
         * Perform new list action
         *
         * @returns {Promise}
         */
        performNewListAction: function () {
            if (!this._isActionValid({})) {
                return $.Deferred().reject().promise();
            }

            return this._super();
        },

        /**
         * Get action data
         *
         * @returns {Object}
         * @protected
         */
        _getActionData: function (list) {
            return _.extend(this._super(list), {
                'product_data': JSON.stringify(this._getProductData())
            });
        },

        /**
         * Get product data
         *
         * @returns {Object}
         * @protected
         */
        _getProductData: function () {
            var productData = {
                sku: this.sku
            },
            productOptions = this._getProductOptions();

            if (productOptions) {
                productData.options = productOptions;

                if (productOptions.qty) {
                    productData.qty = productOptions.qty;
                }
            }

            return productData;
        },

        /**
         * Get product form
         *
         * @returns {*|jQuery|HTMLElement}
         * @protected
         */
        _getProductForm: function () {
            return $(this.productFormSelector);
        },

        /**
         * Get product options
         *
         * @returns string
         * @protected
         */
        _getProductOptions: function () {
            return this._getProductForm().serialize();
        },

        /**
         * Return loaded files information
         *
         * @returns {Object}
         * @protected
         */
        _getLoadedFiles: function () {
            var files = {};

            $.each(this._getProductForm().find('input[type="file"]'), function (index, input) {
                if (input.value) {
                    files[input.name] = input.files;
                }
            });

            return files;
        }
    });
});
