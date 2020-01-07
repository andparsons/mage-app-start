/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'Magento_Ui/js/grid/filters/filters'
], function (ko, gridFilters) {
    'use strict';

    return gridFilters.extend({
        defaults: {
            template: 'Magento_Company/users/grid/filters/filters',
            showAllUsers: ko.observable(false),
            showActiveUsers: ko.observable(true)
        },

        /**
         * Sets filter for status field to 'active'.
         */
        setStatusActive: function () {
            this.showAllUsers(false);
            this.showActiveUsers(true);
            this.filters.status = this.params.statusActive;
            this.apply();
        },

        /**
         * Sets filter for status field to 'inactive'.
         */
        setStatusInactive: function () {
            this.showAllUsers(false);
            this.showActiveUsers(false);
            this.filters.status = this.params.statusInactive;
            this.apply();
        },

        /**
         * Clears filters data.
         */
        clear: function () {
            this.showAllUsers(true);
            this._super(null);
        }
    });
});
