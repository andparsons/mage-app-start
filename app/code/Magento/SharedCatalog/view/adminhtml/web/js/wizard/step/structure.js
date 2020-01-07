/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'underscore',
    'mage/translate'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            modules: {
                assignColumn: '${ $.assignColumnName }',
                categoryTree: '${ $.categoryTreeName }',
                provider: '${ $.providerName }',
                treeProvider: '${ $.treeProviderName }'
            },
            notificationMessage: {
                text: null,
                error: null
            },
            listens: {
                '${ $.assignColumnName }:reassigned': 'reloadAll',
                '${ $.categoryTreeName }:reassigned': 'reloadAll'
            },
            treeSelector: ''
        },

        /**
         * Reload listing and category tree
         *
         * @returns {Structure}
         */
        reloadAll: function () {
            this._reloadProductListing()
                ._reloadCategoryTree();

            return this;
        },

        /**
         * Reload product listing
         *
         * @returns {Structure}
         * @private
         */
        _reloadProductListing: function () {
            this.provider('reload', {
                refresh: true
            });

            return this;
        },

        /**
         * Reload category tree
         *
         * @returns {Structure}
         * @private
         */
        _reloadCategoryTree: function () {
            this.treeProvider('reload', {
                refresh: true
            });

            return this;
        },

        /**
         * Render step
         * @param {Object} wizard
         */
        render: function (wizard) {
            this.wizard = wizard;
        }
    });
});
