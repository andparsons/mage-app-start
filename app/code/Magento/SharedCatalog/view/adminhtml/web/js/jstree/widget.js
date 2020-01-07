/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'mage/template',
    'mage/translate',
    'jquery/ui',
    'jsTreeNew'
], function ($, _, mageTemplate) {
    'use strict';

    $.widget('mage.jstreeWidget', {
        options: {
            jstree: {
                options: {
                    plugins: [],
                    core: {
                        data: {}
                    }
                }
            },
            nodeTextTemplate: ''
        },

        /**
         * Fired when widget initialization start
         *
         * @private
         */
        _create: function () {},

        /**
         * Init tree
         */
        initTree: function () {
            var self = this;

            this.onTreeInit(function () {
                self._treeObject = $(this).jstree(true);
            });
            this.element.jstree(this.options.jstree.options);
        },

        /**
         * Set tree data and refresh
         *
         * @param {Object} data
         */
        setTreeData: function (data) {
            var tree = this._getTreeObject();

            this._prepareTreeNodeData(data);
            tree.settings.core.data = data;
            this.refreshTree();
        },

        /**
         * Refresh tree
         *
         * @private
         */
        refreshTree: function () {
            this._getTreeObject().refresh(true);
        },

        /**
         * Prepare tree data
         *
         * @param {Object} node
         * @private
         */
        _prepareTreeNodeData: function (node) {
            this._prepareTreeNodeText(node);
            this._prepareTreeNodeState(node);

            if (_.isArray(node.children)) {
                _.each(node.children, function (childNode) {
                    childNode = this._prepareTreeNodeData(childNode);
                    node.state.opened = node.state.opened || childNode.state.opened;
                }, this);
            }

            return node;
        },

        /**
         * Prepare tree node text
         *
         * @param {Object} node
         * @returns {*}
         * @private
         */
        _prepareTreeNodeText: function (node) {
            node.text = mageTemplate(this.options.nodeTextTemplate, this._getNodeTextTemplateData(node));

            return node;
        },

        /**
         * Get node text template data
         *
         * @param {Object} node
         * @returns {Object}
         * @private
         */
        _getNodeTextTemplateData: function (node) {
            return this._getNodeData(node);
        },

        /**
         * Prepare node state
         *
         * @param {Object} node
         * @returns {*}
         * @private
         */
        _prepareTreeNodeState: function (node) {
            return node;
        },

        /**
         * Get node custom data
         *
         * @param {Object} node
         * @returns {Object}
         * @private
         */
        _getNodeData: function (node) {
            return node.data || {};
        },

        /**
         * Get tree object
         *
         * @returns {*}
         * @private
         */
        _getTreeObject: function () {
            return this._treeObject;
        },

        /**
         * On tree init
         *
         * @param {Function} callback
         */
        onTreeInit: function (callback) {
            this.element.on(
                'init.jstree',
                callback
            );
        },

        /**
         * On tree loaded
         *
         * @param {Function} callback
         */
        onTreeLoaded: function (callback) {
            this.element.on(
                'loaded.jstree',
                callback
            );
        },

        /**
         * On node check
         *
         * @param {Function} callback
         */
        onNodeCheck: function (callback) {
            this.element.on(
                'check_node.jstree',
                callback
            );
        },

        /**
         * On node uncheck
         *
         * @param {Function} callback
         */
        onNodeUncheck: function (callback) {
            this.element.on(
                'uncheck_node.jstree',
                callback
            );
        },

        /**
         * On node activate
         *
         * @param {Function} callback
         */
        onNodeActivate: function (callback) {
            this.element.on(
                'activate_node.jstree',
                callback
            );
        },

        /**
         * On select all
         *
         * @param {Function} callback
         */
        onSelectAll: function (callback) {
            this.element.on(
                'assign_all',
                callback
            );
        },

        /**
         * On deselect all
         *
         * @param {Function} callback
         */
        onDeselectAll: function (callback) {
            this.element.on(
                'unassign_all',
                callback
            );
        },

        /**
         * On node open
         *
         * @param {Function} callback
         */
        onNodeOpen: function (callback) {
            this.element.on(
                'after_open.jstree',
                callback
            );
        },

        /**
         * On node close
         *
         * @param {Function} callback
         */
        onNodeClose: function (callback) {
            this.element.on(
                'after_close.jstree',
                callback
            );
        }
    });

    return $.mage.jstreeWidget;
});
