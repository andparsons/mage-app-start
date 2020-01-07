/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/select'
], function ($, _, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            formName: 'company_form.company_form',
            modules: {
                modalProvider: '${ $.modalProvider }'
            },
            ignoreProperties: [
                'customer',
                'is_active',
                'is_company_user'
            ],
            customerData: {},
            prevCustomerId: null
        },

        /**
         * Request the customer data by email and website.
         */
        loadCustomerData: function () {
            //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            var data = {
                email: this.source.get('data.company_admin.email'),
                companyId: this.source.get('data.id'),
                website_id: this.source.get('data.company_admin.website_id')
            };
            //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

            $.ajax({
                url: this.addUserUrl,
                type: 'get',
                data: data,
                dataType: 'json',
                context: this,
                async: false,
                showLoader: true
            }).done(this.onLoadCustomerData);
        },

        /**
         * Verify the customer data and update fieldset source.
         *
         * @param {Object} data - Server response.
         */
        onLoadCustomerData: function (data) {
            _.extend(this.validationParams, data);
            this.customerData = data;

            if (!data.error && this.validate().valid) {
                if (this.source.get('data.id')) {
                    this.modalProvider().openModal();
                } else {
                    this.updateSource();
                }
            }
        },

        /**
         * Change the output format of data.
         *
         * @param {Object} data - Admin data.
         * @returns {Object} Updated data.
         * @private
         */
        _normalizeAdminData: function (data) {
            var filtered = {},
                field;

            for (field in data) {
                if (this.ignoreProperties.indexOf(field) === -1) {
                    filtered[field] = this.normalizeData(data[field]);
                }
            }

            return filtered;
        },

        /**
         * User chose another website.
         */
        onUpdate: function () {
            if (this.hasChanged()) {
                this.loadCustomerData();
            }
        },

        /**
         * Update form data based on received customer.
         *
         * @return void
         */
        updateSource: function () {
            var data;

            this.overload();

            if (this.prevCustomerId || this.customerData.customer) {
                //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                data = _.extend(
                    this.customerData,
                    {
                        email: this.source.get('data.company_admin.email'),
                        website_id: this.value(),
                        gender: this.customerData.gender || '3'
                    }
                );
                //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                this.source.set('data.company_admin', data);
                this.setInitialValue();
            }
            this.prevCustomerId = this.customerData.customer;
        }
    });
});
