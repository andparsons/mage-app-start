/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/lib/validation/validator',
    'mage/translate'
], function ($, validator, $t) {
    'use strict';

    /**
     * Compare websites ids
     */
    function compareIds(currentId, rowId) {
        return currentId === rowId || currentId === 0 || rowId === 0;
    }

    return function (target) {

        /**
         * Validate shared catalog name length.
         */
        validator.addRule(
            'max-characters',
            function (value, params) {
                if ($.isNumeric(params)) {
                    return value.length <= parseInt(params, 10);
                }

                return true;
            },
            $t('The maximum allowed catalog name length is {0} characters.')
        );

        /**
         * Validate duplicate values of tier price in shared catalog.
         */
        validator.addRule(
            'validate-duplicate-values',
            function (value, params, data) {
                var isDuplicateValues = data.tierPriceData.filter(function (rowData) {
                    if (data.currentRowData && !rowData.delete && parseFloat(rowData.qty) === parseFloat(value) &&
                        data.currentRowData['record_id'] !== rowData['record_id'] &&
                        compareIds(data.currentRowData['website_id'], rowData['website_id'])
                    ) {
                        return true;
                    }
                });

                return !isDuplicateValues.length;
            },
            $t('Conflicting row.')
        );

        return target;
    };
});
