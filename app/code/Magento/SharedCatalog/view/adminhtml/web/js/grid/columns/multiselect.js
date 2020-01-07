/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/multiselect'
], function (Multiselect) {
    'use strict';

    return Multiselect.extend({
        defaults: {
            bodyTmpl: 'Magento_SharedCatalog/grid/cells/multiselect',
            enableStateName: 'custom_price_enabled'
        },

        /**
         * Get record custom price value
         *
         * @param {Object} record
         * @param {String} index
         * @returns {String}
         */
        getValue: function (record, index) {
            return record[index];
        }
    });
});
