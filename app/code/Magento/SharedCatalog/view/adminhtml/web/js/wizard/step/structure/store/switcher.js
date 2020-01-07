/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_SharedCatalog/js/store/switcher',
    'jquery',
    'underscore',
    'mage/translate'
], function (StoreSwitcher, $, _) {
    'use strict';

    return StoreSwitcher.extend({
        defaults: {
            modules: {
                treeProvider: '${ $.treeProvider }'
            },
            exports: {
                selectedStore: '${ $.treeProvider }:params.filters.store'
            },
            listens: {
                selectedStore: '_onSelectedStoreChanged'
            },
            storeElementSelector: ''
        },

        /**
         * Initialize component
         */
        initialize: function () {
            _.bindAll(this, '_onSelectedStoreChanged');

            this._super();
        },

        /**
         * On selected store changed
         *
         * @private
         */
        _onSelectedStoreChanged: function () {
            this._getStoreElement().val(this.selectedStore().id);
        },

        /**
         * Get store form element
         *
         * @returns {jQuery}
         * @private
         */
        _getStoreElement: function () {
            return $(this.storeElementSelector);
        }
    });
});
