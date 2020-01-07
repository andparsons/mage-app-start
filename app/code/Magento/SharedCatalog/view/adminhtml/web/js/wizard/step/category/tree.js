/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'uiLayout',
    'Magento_Ui/js/lib/spinner',
    'rjsResolver',
    'jquery',
    'underscore',
    'mage/translate',
    'jsTreeWidget'
], function (Component, layout, loader, resolver, $, _) {
    'use strict';

    return Component.extend({
        defaults: {
            treeConfig: {

            },
            modules: {
                listingFilters: '${ $.listingFilters }',
                provider: '${ $.provider }'
            },
            imports: {
                treeData: '${ $.provider }:data'
            },
            listens: {
                '${ $.provider }:reload': '_onBeforeReload',
                '${ $.provider }:reloaded': '_onDataReloaded'
            },
            treeContainerSelector: '',

            isWidgetLoaded: false
        },

        /**
         * Initialize component
         */
        initialize: function () {
            _.bindAll(this, '_onTreeNodeActivate', '_onTreeLoaded', 'initTree');

            return this._super();
        },

        /**
         * Init tree widget
         *
         * @returns {CategoryTree} Chainable
         */
        initTree: function () {
            this._treeElement = $(this.treeContainerSelector);
            this._treeWidget = $.mage.jstree(this.treeConfig, this._treeElement);
            this._observeTreeEvents();
            this._treeWidget.initTree();

            return this;
        },

        /**
         * Observe category tree events
         *
         * @private
         */
        _observeTreeEvents: function () {
            this._treeWidget.onTreeInit(this._onTreeLoaded);
            this._treeWidget.onNodeActivate(this._onTreeNodeActivate);
        },

        /**
         * On category tree node activate
         *
         * @param {Object} e
         * @param {Object} data
         * @private
         */
        _onTreeNodeActivate: function (e, data) {

            /** hook for detect check or activate */
            var isActivate = _.isObject(data.event);

            if (!isActivate) {
                return;
            }
            this._setCategoryFilter(data.node.data.id);
        },

        /**
         * Set category filter
         *
         * @param {Number} categoryId
         * @private
         */
        _setCategoryFilter: function (categoryId) {
            this._addFilterData({
                'category_id': categoryId
            });
        },

        /**
         * Clear category filter
         *
         * @private
         */
        _clearCategoryFilter: function () {
            this._addFilterData({
                'category_id': null
            });
        },

        /**
         * Add filter data
         *
         * @param {Object} data
         * @private
         */
        _addFilterData: function (data) {
            this.listingFilters(function (filter) {
                filter.setData(data, true);
                filter.apply();
            });
        },

        /**
         * Handler of the data providers' 'reload' event.
         */
        _onBeforeReload: function () {
            this.showLoader();
        },

        /**
         * Handler of the data providers' 'reloaded' event.
         */
        _onDataReloaded: function () {
            this._updateTreeData();
            resolver(this.hideLoader, this);
        },

        /**
         * Update tree data
         *
         * @returns {Boolean}
         * @private
         */
        _updateTreeData: function () {

            if (!this.isWidgetLoaded) {
                return false;
            }

            if (!_.isObject(this.treeData.data)) {
                return false;
            }

            if (this._treeWidget.isSelectedButtons) {
                this.treeData.data.data['is_checked'] ?
                    this._treeWidget.setDeselectButton() :
                    this._treeWidget.setSelectButton();

                if (this._treeWidget.expandStateChanged !== true) {
                    this.isFullyOpenedNode(this.treeData.data) ?
                        this._treeWidget.setExpandButton('collapse') :
                        this._treeWidget.setExpandButton('expand');
                }
            }

            this._treeWidget.setTreeData(this.treeData.data);

            return true;
        },

        /**
         * Check if current note is fully opened (including children nodes)
         *
         * @param {Object} node
         * @returns {Boolean}
         */
        isFullyOpenedNode: function (node) {
            var self = this;

            if (node.data['is_opened'] === false) {
                return false;
            } else if (node.children.length) {
                $.each(node.children, function (index, childNode) {
                    return self.isFullyOpenedNode(childNode);
                });
            }

            return true;
        },

        /**
         * Hides loader
         */
        hideLoader: function () {
            loader.get(this.name).hide();
        },

        /**
         * Shows loader
         */
        showLoader: function () {
            loader.get(this.name).show();
        },

        /**
         * On tree loaded
         *
         * @private
         */
        _onTreeLoaded: function () {
            this.isWidgetLoaded = true;
            _.defer(this._updateTreeData.bind(this));
        }
    });
});
