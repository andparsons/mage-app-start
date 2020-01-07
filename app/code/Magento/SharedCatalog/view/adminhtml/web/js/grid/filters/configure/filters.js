/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/filters/filters',
    'underscore'
], function (Filter, _) {
    'use strict';

    return Filter.extend({

        defaults: {
            modules: {
                storeFilter: '${ $.storeFilter }',
                storeSwitcher: '${ $.storeSwitcherProvider }'
            },
            listens: {
                '${ $.storeSwitcherProvider }:selectedStore': 'applyStoreFilter'
            },
            allStoresId: 0,
            inactiveIndex: 'store_id'
        },

        /**
         * Initializes filters component.
         *
         * @returns {Filters} Chainable.
         */
        initialize: function () {
            return this
                ._super()
                .initStoreFilter();
        },

        /**
         * Set store filter initial value.
         *
         * @returns {Filters} Chainable.
         */
        initStoreFilter: function () {
            this.filters['store_id'] = this.storeSwitcher().selectedStoreId;
            this.apply();

            return this;
        },

        /**
         * Apply store filter.
         *
         * @param {Object} selectedStore
         */
        applyStoreFilter: function (selectedStore) {
            var selectedStoreId;

            if (selectedStore.id == this.allStoresId) { //eslint-disable-line eqeqeq
                selectedStoreId = '';
            } else {
                selectedStoreId = selectedStore.id;
            }
            this.storeFilter().value(selectedStoreId);
            this.apply();
        },

        /**
         * Finds filters whith a not empty data
         * and sets them to the 'active' filters array.
         *
         * @returns {Filters} Chainable.
         */
        updateActive: function () {
            var applied = _.without(_.keys(this.applied), this.inactiveIndex);

            this.active = this.elems.filter(function (elem) {
                return _.contains(applied, elem.index);
            });

            return this;
        }
    });
});
