/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/filters/filters',
    'underscore',
    'mage/backend/notification'
], function (Filter, _) {
    'use strict';

    return Filter.extend({
        defaults: {
            inactive: 'websites',
            modules: {
                columns: '${ $.columnsProvider }'
            }
        },

        /**
         * Sets filters data to the applied state.
         */
        apply: function (website) {
            var websiteFilter,
                newFilters;

            if (website && website === this.inactive) {
                websiteFilter = _.pick(this.filters, this.inactive);
                newFilters = _.extend({}, this.applied, websiteFilter);
                _.isEqual(this.filters, this.applied) ? this.columns('hideLoader') : false;
                this.set('applied', newFilters);
            } else {
                this._super();
            }
        },

        /**
         * Finds filters whith a not empty data
         * and sets them to the 'active' filters array.
         *
         * @returns {Filters} Chainable.
         */
        updateActive: function () {
            var applied = _.without(_.keys(this.applied), this.inactive);

            this.active = this.elems.filter(function (elem) {
                return _.contains(applied, elem.index);
            });

            return this;
        }
    });
});
