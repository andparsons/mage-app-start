/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'itemTable'
], function ($, itemTable) {
    'use strict';

    $.widget('mage.quickOrderItemTable', itemTable, {
        options: {
            itemRenderEvent: 'itemRendered',
            itemsRenderCallbacks: {}
        },

        /**
         * @inheritdoc
         */
        _add: function (event, data) {
            var newRowIndex = this.rowIndex + 1;

            this.options.itemsRenderCallbacks[newRowIndex] = data ? data.callback : function () {};

            this._super();
        },

        /**
         * @inheritdoc
         */
        _bind: function () {
            var handlers = {};

            /**
             * @param {jQuery.Event} event
             * @param {Object} element
             */
            handlers[this.options.itemRenderEvent] = function (event, element) {
                var rowIndex = element.options.rowIndex;

                if (this.options.itemsRenderCallbacks[rowIndex]) {
                    this.options.itemsRenderCallbacks[rowIndex].apply();
                }
            };

            this._on(handlers);

            this._super();
        }
    });

    return $.mage.quickOrderItemTable;
});
