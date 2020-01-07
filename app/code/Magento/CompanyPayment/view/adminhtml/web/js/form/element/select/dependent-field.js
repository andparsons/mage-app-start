/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {
        var itemsDataRole = $(config.selector);

        /**
         * Change multiselect disabled value
         *
         * @param {Boolean} flag
         * @private
         */
        function _disableMultiselect(flag) {
            itemsDataRole.prop('disabled', flag);
        }

        if ($(element).val() === '0') {
            _disableMultiselect(true);
            itemsDataRole.find('option').prop('selected', true);
        }

        $(element).change(function () {
            if ($(element).val() === '0') {
                _disableMultiselect(true);
            } else {
                _disableMultiselect(false);
            }
        });
    };
});
