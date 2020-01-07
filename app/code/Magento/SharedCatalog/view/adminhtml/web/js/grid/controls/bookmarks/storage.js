/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/controls/bookmarks/storage'
], function (Storage) {
    'use strict';

    return Storage.extend({
        defaults: {
            isSaveEnabled: true
        },

        /**
         * Sends request to store specified data.
         *
         * @param {String} path - Path by which data should be stored.
         * @param {*} value - Value to be sent.
         */
        set: function (path, value) {
            if (!this.isSaveEnabled) {
                return;
            }
            this._super(path, value);
        }
    });
});
