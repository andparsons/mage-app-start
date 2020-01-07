/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_SharedCatalog/js/wizard/step/category/tree',
    'uiLayout',
    'jquery',
    'underscore',
    'mage/translate',
    'Magento_SharedCatalog/js/wizard/step/structure/category/tree/widget'
], function (CategoryTree, layout, $, _) {
    'use strict';

    return CategoryTree.extend({
        defaults: {
            rootCategoryId: 1,
            clientConfig: {
                component: 'Magento_Ui/js/grid/editing/client',
                name: '${ $.name }_client'
            },
            modules: {
                client: '${ $.clientConfig.name }'
            }
        },

        /**
         * Initialize component
         */
        initialize: function () {
            _.bindAll(this,
                '_onTreeNodeCheck',
                '_onTreeNodeUncheck',
                '_onAssignDone',
                '_onTreeSelectAll',
                '_onTreeDeselectAll',
                '_onTreeNodeTriggered'
            );

            this._super()
                .initClient();
        },

        /**
         * Initializes client component
         *
         * @returns {CategoryTree} Chainable
         */
        initClient: function () {
            layout([this.clientConfig]);

            return this;
        },

        /**
         * Init tree widget
         *
         * @returns {CategoryTree} Chainable
         */
        initTree: function () {
            this._treeElement = $(this.treeContainerSelector);
            this._treeWidget = $.mage.sharedCatalogStructureCategory(this.treeConfig, this._treeElement);
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
            this._super();
            this._treeWidget.onNodeCheck(this._onTreeNodeCheck);
            this._treeWidget.onNodeUncheck(this._onTreeNodeUncheck);
            this._treeWidget.onSelectAll(this._onTreeSelectAll);
            this._treeWidget.onDeselectAll(this._onTreeDeselectAll);
            this._treeWidget.onNodeOpen(this._onTreeNodeTriggered);
            this._treeWidget.onNodeClose(this._onTreeNodeTriggered);
        },

        /**
         * On tree node uncheck observer
         *
         * @param {Object} e
         * @param {Object} data
         * @private
         */
        _onTreeNodeCheck: function (e, data) {
            this._setCategoryAssign(data.node.data.id, 1, 0);
            this._changeExpandState(false);
        },

        /**
         * On tree node uncheck observer
         *
         * @param {Object} e
         * @param {Object} data
         * @private
         */
        _onTreeNodeUncheck: function (e, data) {
            this._setCategoryAssign(data.node.data.id, 0, 0);
            this._changeExpandState(true);
        },

        /**
         * On tree select all
         *
         * @private
         */
        _onTreeSelectAll: function () {
            this._setCategoryAssign(this.rootCategoryId, 1, 1);
            this._changeExpandState(false);
        },

        /**
         * On tree deselect all
         *
         * @private
         */
        _onTreeDeselectAll: function () {
            this._setCategoryAssign(this.rootCategoryId, 0, 1);
            this._changeExpandState(true);
        },

        /**
         * On tree node open/closed
         *
         * @private
         */
        _onTreeNodeTriggered: function () {
            this._changeExpandState(true);
        },

        /**
         * Set category assigned state
         *
         * @param {Number} categoryId
         * @param {Boolean} isAssign
         * @param {Boolean} isIncludeSubcategories
         * @private
         */
        _setCategoryAssign: function (categoryId, isAssign, isIncludeSubcategories) {
            this.showLoader();
            this.client()
                .save(this._prepareRequestData(categoryId, isAssign, isIncludeSubcategories))
                .done(this._onAssignDone);
        },

        /**
         * Prepare request data for assign
         *
         * @param {Number} categoryId
         * @param {Boolean} isAssign
         * @param {Boolean} isIncludeSubcategories
         * @returns {Object}
         * @private
         */
        _prepareRequestData: function (categoryId, isAssign, isIncludeSubcategories) {
            return {
                'category_id': categoryId,
                'is_assign': isAssign,
                'is_include_subcategories': isIncludeSubcategories
            };
        },

        /**
         *
         * @private
         */
        _onAssignDone: function () {
            this.hideLoader();
            this.trigger('reassigned');

        },

        /**
         * Change state for Expand/Collapse button
         *
         * @param {Boolean} isFinalChange
         * @private
         */
        _changeExpandState: function (isFinalChange) {
            if (this._treeWidget._treeObject.element.find('li[aria-expanded=false]').length > 0) {
                this._treeWidget.setExpandButton('expand');
            } else {
                this._treeWidget.setExpandButton('collapse');
            }
            this._treeWidget.expandStateChanged = isFinalChange;
        }
    });
});
