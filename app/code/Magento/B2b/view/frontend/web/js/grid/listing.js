/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/listing'
], function (gridListing) {
    'use strict';

    return gridListing.extend({
        defaults: {
            template: 'Magento_B2b/grid/listing'
        },

        /**
         * @return {*}
         */
        getTableClass: function () {
            return this['table_css_class'];
        }
    });
});
