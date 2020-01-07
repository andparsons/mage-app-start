/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    return {
        /**
         * Is valid price input event
         *
         * @param {Object} e
         * @returns {Boolean}
         */
        isDigits: function (e) {
            if (_.contains([8, 9, 27, 13, 110, 190], e.keyCode) ||
                e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true) ||
                e.keyCode >= 35 && e.keyCode <= 40) {
                return true;
            } else
            if ((e.shiftKey || (e.keyCode < 46 || e.keyCode > 57)) &&
                (e.keyCode < 96 || e.keyCode > 105)) {
                return false;
            }

            return true;
        }
    };
});
