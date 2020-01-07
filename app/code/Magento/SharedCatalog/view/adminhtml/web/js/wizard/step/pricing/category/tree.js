/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_SharedCatalog/js/wizard/step/category/tree',
    'jquery',
    'underscore',
    'mage/translate',
    'Magento_SharedCatalog/js/wizard/step/pricing/category/tree/widget'
], function (CategoryTree, $) {
    'use strict';

    return CategoryTree.extend({
        defaults: {

        },

        /**
         * Init tree widget
         *
         * @returns {CategoryTree} Chainable
         */
        initTree: function () {
            this._treeElement = $(this.treeContainerSelector);
            this._treeWidget = $.mage.sharedCatalogPricingCategory(this.treeConfig, this._treeElement);
            this._observeTreeEvents();
            this._treeWidget.initTree();

            return this;
        }
    });
});
