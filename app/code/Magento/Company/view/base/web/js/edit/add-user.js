/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/lib/spinner',
    'Magento_Ui/js/form/element/abstract'
], function ($, _, loader, Abstract) {
    'use strict';

    var DEFAULT_GENDER = '3',
        OMITTED_FIELDS = [
            'is_active',
            'is_company_user'
        ];

    return Abstract.extend({
        defaults: {
            companyId: '',
            companyAdmin: {},
            formName: 'company_form.company_form',
            elementTmpl: 'Magento_Company/edit/email-field',
            modules: {
                modalProvider: '${ $.modalProvider }'
            },
            validationParams: {},
            prevCustomerId: null
        },

        /**
         * Init UI component.
         *
         * @returns {Component} Chainable.
         */
        initialize: function () {
            return this._super().setCompanyId();
        },

        /**
         * Set current company id.
         *
         * @returns {Component} Chainable.
         */
        setCompanyId: function () {
            if (this.source) {
                this.companyId = this.source.get('data.id');
            }

            return this;
        },

        /**
         *  Callback when value is changed by user
         *
         *  @param {Object} ui - UI component.
         *  @param {Object} e - event.
         */
        userChanges: function (ui, e) {
            this._super();
            this.clearValidationParams();
            this.value(e.target.value);

            if (this.hasChanged() && this.hasData() && this.isValid()) {
                loader.get(this.formName).show();
                this.getCustomerData();
            }
        },

        /**
         * Request the customer data by email.
         */
        getCustomerData: function () {
            //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            var data = {
                email: this.value(),
                companyId: this.companyId,
                website_id: this.source.get('data.company_admin.website_id')
            };
            //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

            $.ajax({
                url: this.getCustomerDataUrl,
                type: 'get',
                data: data,
                dataType: 'json',
                context: this,
                async: false,
                showLoader: true
            }).done(this.onGetCustomerData);
        },

        /**
         * Verify the customer data and update fieldset source.
         *
         * @param {Object} data - Server response.
         */
        onGetCustomerData: function (data) {
            this.companyAdmin = this._normalizeAdminData(data);

            if (_.isObject(data)) {
                _.extend(this.validationParams, data);
            }
            loader.get(this.formName).hide();

            if (data.error || !this.isValid()) {
                return false;
            }

            // 'edit company' route
            if (this.companyId) {
                this.modalProvider().openModal();
            // 'new company' route
            } else {
                this.updateSource();
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
            return _.mapObject(_.omit(data, OMITTED_FIELDS), this.normalizeData);
        },

        /**
         * Check validation.
         *
         * @returns {Boolean} Validation result.
         */
        isValid: function () {
            return this.validate().valid;
        },

        /**
         * Clear validation params.
         */
        clearValidationParams: function () {
            this.validationParams = {};
        },

        /**
         * Update the form data source.
         */
        updateSource: function () {
            var data = {};

            if (this.prevCustomerId || this.companyAdmin.customer) {
                //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                data = _.extend(this.companyAdmin, {
                    email: this.value(),
                    gender: this.companyAdmin.gender || DEFAULT_GENDER,
                    website_id: this.source.get('data.company_admin.website_id')
                });
                //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                this.source.set('data.company_admin', data);
            }
            this.prevCustomerId = this.companyAdmin.customer;
        }
    });
});
