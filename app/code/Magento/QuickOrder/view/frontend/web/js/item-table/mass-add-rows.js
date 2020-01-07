/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'jquery'
], function (_, $) {
    'use strict';

    /**
     * Build callback for rendering of new table item.
     *
     * @param {Object} data
     * @returns {Function}
     */
    function buildAddNewRowCallback(data) {
        return _.once(function () {
            !--data.count && data.defer.resolve();
        });
    }

    return {
        /**
         * Add new rows to item table.
         *
         * @param {HTMLElement} itemTableElement
         * @param {Number} count
         */
        addNewRows: function (itemTableElement, count) {
            var data = {
                count: count,
                defer: $.Deferred()
            };

            while (count--) {
                itemTableElement.trigger(
                    'addNewRow',
                    {
                        callback: buildAddNewRowCallback(data)
                    }
                );
            }

            if (!data.count) {
                data.defer.resolve();
            }

            return data.defer.promise();
        }
    };
});
