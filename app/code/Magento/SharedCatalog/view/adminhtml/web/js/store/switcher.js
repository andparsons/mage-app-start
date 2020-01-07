/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiElement',
    'underscore',
    'mage/translate'
], function (Element, _) {
    'use strict';

    var Switcher = Element.extend({
        defaults: {
            template: 'Magento_SharedCatalog/store/switcher',
            stores: [],
            selectedStoreId: null,
            selectedStore: null
        },

        /**
         * Initializes model instance.
         *
         * @returns {Switcher} Chainable.
         */
        initialize: function () {
            this._super()
                .initSelectedStore();

            return this;
        },

        /**
         * Initial observerable
         * @returns {*}
         */
        initObservable: function () {
            this._super().observe('selectedStore');

            return this;
        },

        /**
         * Init selected store
         *
         * @returns {Switcher}
         */
        initSelectedStore: function () {
            if (!this.selectedStoreId) {
                this.selectedStoreId = _.first(this.getStores()).id;
            }
            this.setSelectedStoreId(this.selectedStoreId);

            return this;
        },

        /**
         * Set selected store
         *
         * @param {Object} store
         * @returns {Switcher}
         */
        setSelectedStore: function (store) {
            return this.setSelectedStoreId(store.id);
        },

        /**
         * Set selected store id
         *
         * @param {Number} storeId
         * @returns {Switcher}
         */
        setSelectedStoreId: function (storeId) {
            this.selectedStore(this.getStoreById(storeId));

            return this;
        },

        /**
         * Get store by id
         *
         * @param {Number} storeId
         * @returns {Object}
         */
        getStoreById: function (storeId) {
            return _.findWhere(this.getStores(), {
                id: storeId
            });
        },

        /**
         * Get stores array
         *
         * @returns {Array}
         */
        getStores: function () {
            return this.stores;
        },

        /**
         * Is switcher disabled
         *
         * @returns {Boolean}
         */
        isDisabled: function () {
            return !!this.disabled;
        }
    });

    return Switcher;
});
