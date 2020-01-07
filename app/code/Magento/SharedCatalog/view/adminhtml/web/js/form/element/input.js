/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/form/element/abstract'
], function (_, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            listens: {
                '${ $.provider }:data': 'checkValidate'
            },
            modules: {
                parent: '${ $.parentName }'
            },
            validationParams: {}
        },

        /**
         * Initializes tier price component.
         *
         * @returns {TearPrice} Chainable.
         */
        initialize: function () {
            this._super()
                .setValidateParams();

            return this;
        },

        /**
         * Set params for validation.
         */
        setValidateParams: function () {
            this.validationParams.tierPriceData = this.getTierPriceParams();
            this.validationParams.currentRowData = this.getCurrentRowData();
        },

        /**
         * Get params of currently row
         *
         * @returns {Object|Boolean} row params or false
         */
        getCurrentRowData: function () {
            if (!this.currentRow) {
                this.currentRow = this.parent();
            }

            return this.currentRow ? this.currentRow.data() : false;
        },

        /**
         * Get tier price params.
         *
         * @returns {Array} tier price params
         */
        getTierPriceParams: function () {
            return this.source.get('data.tier_price');
        },

        /**
         * Validate field and caching it status.
         */
        validate: function () {
            this.validateStatus = this._super();
        },

        /**
         * Revalidate field if it has invalid status
         */
        checkValidate: function () {
            this.setValidateParams();

            if (this.validateStatus && !this.validateStatus.valid) {
                this.validate();
            }
        }
    });
});
