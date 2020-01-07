/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Magento_Company/users/grid/cells/text-with-dash-placeholder'
        },

        /**
         * Returns value or '-' if it is empty.
         *
         * @returns {String}
         */
        getText: function (value) {
            return value ? value : '\u2014';
        }
    });
});
