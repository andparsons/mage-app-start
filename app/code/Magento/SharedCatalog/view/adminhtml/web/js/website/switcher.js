/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiElement',
    'underscore',
    'mage/translate'
], function (Element, _) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'Magento_SharedCatalog/website/switcher',
            listens: {
                '${ $.provider }:data.websites': '_setWebsites'
            },
            imports: {
                selectedStore: '${ $.structureStoreSwitcher }:selectedStore'
            },
            modules: {
                filterProvider: '${ $.filterProvider }',
                websitesFilterProvider: '${ $.websitesFilterProvider }',
                websitesProvider: '${ $.websitesProvider }'
            },
            selectedWebsiteLabel: '',
            websites: [],
            inactiveIndex: 'websites'
        },

        /**
         * Initial observerable
         * @returns {*}
         */
        initObservable: function () {
            this._super().observe([
                'websites',
                'selectedWebsiteLabel'
            ]);

            return this;
        },

        /**
         * Set changed filter.
         * @param {Object} website - Selected item.
         */
        setSelectedWebsite: function (website) {
            this._setWebsite(website);
        },

        /**
         * Initialization of websites list.
         * @param {Object} data - Server response.
         * @private
         */
        _setWebsites: function (data) {
            var self = this,
                filterWebsites = data.items,
                selectedItem;

            if (parseFloat(this.selectedStore.id)) {
                filterWebsites = filterWebsites.filter(function (website) {
                    return !website['store_ids'] || _.contains(website['store_ids'], self.selectedStore.id);
                });
            }

            if (!parseFloat(this.selectedStore.id) && data.isPriceScopeGlobal) {
                filterWebsites = [data.items[0]];
            }
            selectedItem = _.find(filterWebsites, {
                value: data.selected
            }) || filterWebsites[0];

            this.websites(filterWebsites);
            this._setWebsite(selectedItem);
        },

        /**
         * Change website filter.
         * @param {Object} website - Selected item.
         * @private
         */
        _setWebsite: function (website) {
            this.selectedWebsiteLabel(website.label);
            this.websitesFilterProvider().value(website.value);
            this.filterProvider().apply(this.inactiveIndex);
        }
    });
});
