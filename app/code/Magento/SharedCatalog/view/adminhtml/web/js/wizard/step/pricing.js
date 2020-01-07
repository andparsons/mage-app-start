/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'underscore',
    'mage/translate'
], function (Component, $) {
    'use strict';

    return Component.extend({
        defaults: {
            modules: {
                provider: '${ $.providerName }',
                treeProvider: '${ $.treeProviderName }'
            },
            notificationMessage: {
                text: null,
                error: null
            },
            listens: {
                '${ $.massActionName }:price-updated': '_reloadProductListing',
                '${ $.customPriceColumnName }:price-updated': '_reloadProductListing',
                '${ $.tierPriceFormRendererName }:price-updated': '_reloadProductListing'
            },
            wizardStepSelector: '[data-role=step-wizard-dialog]',
            nextLabelText: $.mage.__('Generate Catalog')
        },

        /**
         * Reload listing and category tree
         *
         * @returns {Pricing}
         */
        reloadAll: function () {
            this._reloadProductListing()
                ._reloadCategoryTree();

            return this;
        },

        /**
         * Reload product listing
         *
         * @returns {Pricing}
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
         * @returns {Pricing}
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
         */
        render: function () {
            this.reloadAll();
        },

        /**
         * Force step
         */
        force: function () {
            this.trigger('close-modal', this.closeModal.bind(this));
        },

        /**
         * Close modal
         */
        closeModal: function () {
            $(this.wizardStepSelector).trigger('closeModal');
        }
    });
});
