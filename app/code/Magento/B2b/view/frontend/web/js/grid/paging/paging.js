/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/paging/paging'
], function (gridPaging) {
    'use strict';

    return gridPaging.extend({
        defaults: {
            template: 'Magento_B2b/grid/paging/paging',
            sizesConfig: {
                template: 'Magento_B2b/grid/paging/sizes'
            }
        },

        /**
         * @return {Number}
         */
        getFirstNum: function () {
            return this.pageSize * (this.current - 1) + 1;
        },

        /**
         * @return {*}
         */
        getLastNum: function () {
            if (this.isLast()) {
                return this.totalRecords;
            }

            return this.pageSize * this.current;
        },

        /**
         * @return {Array}
         */
        getPages: function () {
            var pagesList = [],
                i;

            for (i = 1; i <= this.pages; i++) {
                pagesList.push(i);
            }

            return pagesList;
        }
    });
});
