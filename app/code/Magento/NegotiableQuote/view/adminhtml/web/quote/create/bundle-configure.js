/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global Class, $ */
define(['Magento_NegotiableQuote/catalog/product/composite/configure'], function () {
    'use strict';

    return function (param) {

        var BundleControl = Class.create();

        BundleControl.prototype = {

            /**
             * Initialize config
             *
             * @param {Object} config
             *
             * @private
             */
            initialize: function (config) {
                this.config = config;
            },

            /**
             * Callback for change select
             *
             * @param {Object} selection
             *
             * @private
             */
            changeSelection: function (selection) {
                var parts, optionId, showQtyInput, options, selectionOptions;

                if (selection.multiple) {
                    return;
                }
                parts = selection.id.split('-');
                optionId = parts[2];
                showQtyInput = selection.value && selection.value != 'none'; //eslint-disable-line eqeqeq
                options = this.config.options[optionId];
                selectionOptions = options && options.selections && options.selections[selection.value] || {};

                selectionOptions['can_change_qty'] = Number(selectionOptions['can_change_qty']) && showQtyInput;
                this.updateQtyInput(optionId, selectionOptions);
            },

            /**
             * Update input for qty param
             *
             * @param {String} optionId
             * @param {Object} selectionOptions
             *
             * @private
             */
            updateQtyInput: function (optionId, selectionOptions) {
                var elem = $('bundle-option-' + optionId + '-qty-input'),
                    defaultQty = Number(selectionOptions['default_qty']);

                if (!elem) {
                    return;
                }

                if (selectionOptions['can_change_qty']) {
                    elem.removeClassName('qty-disabled');
                    elem.disabled = false;
                    elem.value = defaultQty || 1;
                } else {
                    elem.addClassName('qty-disabled');
                    elem.disabled = true;
                    elem.value = defaultQty || 0;
                }
            },

            /**
             * Update defaults options
             *
             * @private
             */
            updateForDefaults: function () {
                var optionId, selection;

                for (optionId in this.config.options) { //eslint-disable-line guard-for-in
                    selection = $('bundle-option-' + optionId);

                    if (selection) {
                        this.changeSelection(selection);
                    }
                }
            }
        };

        window.ProductConfigure.bundleControl = new BundleControl(param.config);
    };
});
