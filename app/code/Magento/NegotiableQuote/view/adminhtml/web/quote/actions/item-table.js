/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/template',
    'jquery/ui'
], function ($, mageTemplate) {
    'use strict';

    $.widget('mage.itemTable', {
        options: {
            addBlock: '[data-template="add-block"]',
            addBlockData: {},
            addEvent: 'change',
            removeEvent: 'click',
            addField: 'product-sku',
            btnAddQuote: '[data-role="add-to-quote"]',
            addSelector: '[data-role="wrap"]',
            deleteItem: '[data-role="delete"]',
            itemsSelector: '[data-container="items"]',
            deleteMarker: '[data-delete]',
            keepLastRow: true
        },

        /**
         * This method adds a new instance of the block to the items.
         *
         * @private
         */
        _add: function () {
            var addedBlock;

            this.rowIndex++;
            this.options.addBlockData.rowIndex = this.rowIndex;

            addedBlock = $(this.addBlockTmpl({
                data: this.options.addBlockData
            }));

            this.element.find(this.options.itemsSelector).append(addedBlock);
            addedBlock.trigger('contentUpdated');
            this._getDeletableItems();

        },

        /**
         * This method binds elements found in this widget.
         *
         * @private
         */
        _bind: function () {
            var handlers = {};

            handlers[this.options.addEvent + ' ' + this.options.addSelector] = '_addNewRow';
            handlers[this.options.removeEvent + ' ' + this.options.deleteItem] = '_onDeleteItem';
            this._on(handlers);
        },

        /**
         * This method constructs a new widget.
         *
         * @private
         */
        _create: function () {
            this._bind();

            this.addBlockTmpl = mageTemplate(this.options.addBlock);
            this.rowIndex = -1;

            if (this.options.addBlockData == null || typeof this.options.addBlockData !== 'object') {
                this.options.addBlockData = {};
            }

            this.btnAddQuote = $(this.options.btnAddQuote);

            this._add();
        },

        /**
         * This method enable button and add new row.
         *
         * @param {Object} e
         *
         * @private
         */
        _addNewRow: function (e) {
            var element = $(e.target),
                skuRow;

            if (element.data('role') === this.options.addField) {
                skuRow = element.closest(this.options.addSelector);
            }

            if (skuRow && element.val() !== '' && this._checkOnLast(skuRow)) {
                this._add();
                this.btnAddQuote.removeAttr('disabled');
                this.btnAddQuote.removeClass('disabled');
            }
        },

        /**
         * This method returns the list of widgets associated with deletable items from the container (direct children
         * only).
         *
         * @private
         *
         * @return {Array}
         */
        _getDeletableItems: function () {
            return this.element.find(this.options.itemsSelector + '>' + this.options.addSelector);
        },

        /**
         * Check whether the last element.
         *
         * @param {Object} row
         *
         * @private
         *
         * @return {Boolean}
         */
        _checkOnLast: function (row) {
            var rows = this._getDeletableItems(),
                lastRow = rows[rows.length - 1];

            return $(lastRow).data('id') === row.data('id');
        },

        /**
         * This method removes the item associated with the message.
         *
         * @param {Object} e
         *
         * @private
         */
        _onDeleteItem: function (e) {
            var _id = $(e.target).parents(this.options.deleteMarker).data('delete');

            e.stopPropagation();

            if (this._getDeletableItems().length > 1) {
                $(this.element).find('[data-id="' + _id + '"]').remove();
            }
        }
    });

    return $.mage.itemTable;
});
