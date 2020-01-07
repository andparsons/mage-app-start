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

    $.widget('mage.sharedCatalogStructureCategory', jsTreeWidget, {
        options: {
            jstree: {
                options: {
                    plugins: ['checkbox'],
                    core: {
                        'check_callback': true
                    },
                    checkbox: {
                        'tie_selection': false,
                        'three_state': false,
                        cascade: '',
                        'whole_node': false
                    }
                }
            },
            containerClassUndetermined: 'jstree-undetermined-container',
            texts: {
                of: $.mage.__('of'),
                included: $.mage.__('included')
            },
            nodeTextTemplate: '<%- name %><div class="jstree-item-quantity">' +
            '<%- product_assigned %> <%- texts.of %> ' +
            '<%- product_count %> <%- texts.included %></div>',
            buttons: {
                expandSelector: '[data-action="expand-structure-tree"]',
                expandText: $.mage.__('Expand All'),
                collapseText: $.mage.__('Collapse All'),
                selectSelector: '[data-action="select-structure-tree"]',
                selectText: $.mage.__('Select All'),
                deselectText: $.mage.__('Deselect All'),
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
         * Refresh tree
         *
         * @private
         */
        refreshTree: function () {
            this._getTreeObject().refresh(true, function (state) {
                // reset state of checkboxes
                delete state.checkbox;

                return state;
            });
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
                'product_count': data['product_count'],
                texts: this.options.texts
            };
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
            node.state.checked = this._isNodeChecked(node);
            node.state['checkbox_disabled'] = this._isNodeDisabled(node);

            if (_.isArray(node.children)) {
                _.each(node.children, function (childNode) {
                    var isNodeVisible = this._isNodeChecked(childNode);

                    node.state.opened = node.state.opened || isNodeVisible;
                }, this);
            }

            return node;
        },

        /**
         * Is node checked
         *
         * @param {Object} node
         * @private
         */
        _isNodeChecked: function (node) {
            var data = this._getNodeData(node);

            return data['is_checked'];
        },

        /**
         * Is node checked
         *
         * @param {Object} node
         * @private
         */
        _isNodeDisabled: function (node) {
            var data = this._getNodeData(node);

            return !data['is_active'];
        },

        /**
         * Assigns text to buttons
         *
         * @private
         */
        _buttonsText: function () {
            $(this.options.buttons.expandSelector).text(this.options.buttons.expandText);
            $(this.options.buttons.selectSelector).text(this.options.buttons.selectText);
        },

        /**
         * Assigns events to buttons
         *
         * @private
         */
        _setButtonsEvents: function () {
            var buttonState = '';

            this.isSelectedButtons = true;
            $(this.options.buttons.expandSelector).on('click', $.proxy(function () {
                buttonState = $(this.options.buttons.expandSelector).data(this.options.buttons.buttonState);

                if (buttonState === 'expand') {
                    this.element.jstree('open_all');
                    this.setExpandButton('collapse');
                }

                if (buttonState === 'collapse') {
                    this.element.jstree('close_all');
                    this.setExpandButton('expand');
                }
            }, this));
            $(this.options.buttons.selectSelector).on('click', $.proxy(function () {
                buttonState = $(this.options.buttons.selectSelector).data(this.options.buttons.buttonState);

                if (buttonState === 'select') {
                    this.element.trigger('assign_all');
                    this.setSelectButton();
                }

                if (buttonState === 'deselect') {
                    this.element.trigger('unassign_all');
                    this.setDeselectButton();
                }
            }, this));
        },

        /**
         * Set data for expand button
         */
        setExpandButton: function (state) {
            var buttonText = this.options.buttons.expandText;

            if (state === 'collapse') {
                buttonText = this.options.buttons.collapseText;
            } else {
                state = 'expand';
                buttonText = this.options.buttons.expandText;
            }

            $(this.options.buttons.expandSelector).data(this.options.buttons.buttonState, state);
            $(this.options.buttons.expandSelector).text(buttonText);
        },

        /**
         * Set data for button of select
         */
        setSelectButton: function () {
            $(this.options.buttons.selectSelector).data(this.options.buttons.buttonState, 'select');
            $(this.options.buttons.selectSelector).text(this.options.buttons.selectText);
        },

        /**
         * Set data for button of deselect
         */
        setDeselectButton: function () {
            $(this.options.buttons.selectSelector).data(this.options.buttons.buttonState, 'deselect');
            $(this.options.buttons.selectSelector).text(this.options.buttons.deselectText);
        }
    });

    return $.mage.sharedCatalogStructureCategory;
});
