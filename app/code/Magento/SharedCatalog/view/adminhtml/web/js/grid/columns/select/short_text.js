/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiLayout',
    'mage/translate',
    'Magento_Ui/js/grid/columns/select'
], function (_, layout, $t, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            labelMaxLength: 15,
            labelSuffix: '...'
        },

        /**
         * Ment to preprocess data associated with a current columns' field.
         *
         * @param {Object} record - Data to be preprocessed.
         * @returns {String}
         */
        getLabel: function (record) {
            var name = this._super(record);

            if (name && name.length >= this.labelMaxLength - 1) {
                name = name.slice(0, this.labelMaxLength) + this.labelSuffix;
            }

            return name;
        }
    });
});
