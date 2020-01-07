/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'jsTreeWidget',
    'mage/translate'
], function ($, _, jsTreeWidget) {
    'use strict';

    $.widget('mage.sharedCatalogPricingCategory', jsTreeWidget, {
        options: {
            jstree: {
                options: {
                    plugins: []
                }
            },
            texts: {
                products: $.mage.__('products')
            },
            nodeTextTemplate: '<%- name %><div class="jstree-item-quantity">' +
            '<%- product_assigned %> <%- texts.products %></div>',
            buttons: {
                expandSelector: '[data-action="expand-pricing-tree"]',
                expandText: $.mage.__('Expand All'),
                collapseText: $.mage.__('Collapse All'),
                buttonState: 'button-state'
            }
        },

        /**
         * Init tree
         */
        initTree: function () {
            this.element.on('ready.jstree', $.proxy(function () {
                this._setButtonsEvents();
            }, this));
            this._super();
            this._buttonsText();
        },

        /**
         * Get node text template data
         *
         * @param {Object} node
         * @returns {Object}
         * @private
         */
        _getNodeTextTemplateData: function (node) {
            var data = this._getNodeData(node);

            return {
                name: data.name,
                'product_assigned': data['product_assigned'],
                texts: this.options.texts
            };
        },

        /**
         * Refresh tree
         *
         * @private
         */
        refreshTree: function () {
            this._getTreeObject().refresh(true, true);
        },

        /**
         * Prepare node state
         *
         * @param {Object} node
         * @returns {*}
         * @private
         */
        _prepareTreeNodeState: function (node) {
            node.state = node.state || {};
            node.state.opened = true;

            return node;
        },

        /**
         * Assigns text to buttons
         *
         * @private
         */
        _buttonsText: function () {
            $(this.options.buttons.expandSelector).text(this.options.buttons.collapseText);
        },

        /**
         * Assigns events to buttons
         *
         * @private
         */
        _setButtonsEvents: function () {
            var buttonState = '';

            $(this.options.buttons.expandSelector).on('click', $.proxy(function () {
                buttonState = $(this.options.buttons.expandSelector).data(this.options.buttons.buttonState);

                if (buttonState === 'expand') {
                    this.element.jstree('open_all');
                    $(this.options.buttons.expandSelector).data(this.options.buttons.buttonState, 'collapse');
                    $(this.options.buttons.expandSelector).text(this.options.buttons.collapseText);
                }

                if (buttonState === 'collapse') {
                    this.element.jstree('close_all');
                    $(this.options.buttons.expandSelector).data(this.options.buttons.buttonState, 'expand');
                    $(this.options.buttons.expandSelector).text(this.options.buttons.expandText);
                }
            }, this));
        }
    });

    return $.mage.sharedCatalogPricingCategory;
});
