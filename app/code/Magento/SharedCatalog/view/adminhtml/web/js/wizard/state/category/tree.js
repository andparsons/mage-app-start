/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_SharedCatalog/js/wizard/step/pricing/category/tree',
    'jquery',
    'underscore',
    'mage/translate',
    'Magento_SharedCatalog/js/wizard/state/category/tree/widget'
], function (CategoryTree, $) {
    'use strict';

    return CategoryTree.extend({

        /**
         * Initialize component
         */
        initialize: function () {
            this._super();
            this.provider('onParamsChange');

            return this;
        },

        /**
         * Init tree widget
         *
         * @returns {CategoryTree} Chainable
         */
        initTree: function () {
            this._treeElement = $(this.treeContainerSelector);
            this._treeWidget = $.mage.sharedCatalogStateCategory(this.treeConfig, this._treeElement);
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
        }
    });
});
