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

    return Abstract.extend({
        defaults: {
            validationParams: {},
            formName: 'company_form.company_form',
            defaultsValidateParams: {
                'is_company_email_available': true
            },
            listens: {
                value: 'checkEmail'
            }
        },

        /**
         *  Callback when value is changed.
         *
         *  @param {String} value - email value.
         */
        checkEmail: function (value) {
            this.resetValidationParams();

            if (value && !_.isUndefined(this.initialValue) && value !== this.initialValue && this.isValid()) {
                loader.get(this.formName).show();
                this.getCompanyData();
            }
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
         * Request the company data by email.
         */
        getCompanyData: function () {
            var data = {
                'company_email': this.value()
            };

            $.ajax({
                url: this.companyValidateUrl,
                type: 'get',
                data: data,
                dataType: 'json',
                context: this,
                async: false,
                showLoader: true
            }).done(this.onGetCompanyData);
        },

        /**
         * Extend the company data with validate params and validate the field with new params.
         *
         * @param {Object} data - Server response.
         */
        onGetCompanyData: function (data) {
            _.extend(this.validationParams, data);
            this.validate();
            loader.get(this.formName).hide();
        },

        /**
         * Reset validation params to default.
         */
        resetValidationParams: function () {
            _.extend(this.validationParams, this.defaultsValidateParams);
        }
    });
});
