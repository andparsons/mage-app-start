/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    '../authorization'
], function (Component, customerData, authorization) {
    'use strict';

    return Component.extend({
        defaults: {
            logoutUrl: ''
        },

        /**
         * Initialize component
         */
        initialize: function () {
            this._super();

            this.config = customerData.get('company');
            this.config.subscribe(this._updateConfig.bind(this));
        },

        /**
         * Is checkout allowed
         *
         * @returns {Boolean}
         */
        isCheckoutAllowed: function () {
            return !(this.config()['is_checkout_allowed'] === false);
        },

        /**
         * Is company blocked
         *
         * @returns {Boolean}
         */
        isCompanyBlocked: function () {
            return this.config()['is_company_blocked'] === true;
        },

        /**
         * Customer has no company
         *
         * @returns {Boolean}
         */
        hasNoCompany: function () {
            return this.config()['has_customer_company'] === false;
        },

        /**
         * Is users view allowed.
         *
         * @returns {Boolean}
         */
        isUsersViewAllowed: function () {
            return authorization.isAllowed('Magento_Company::users_view');
        },

        /**
         * Is storefront registration allowed.
         *
         * @returns {Boolean}
         */
        isStoreFrontRegistrationAllowed: function () {
            return !!this.config()['is_storefront_registration_allowed'];
        },

        /**
         * Update config handler
         *
         * @param {Object} config
         * @private
         */
        _updateConfig: function (config) {
            if (config['is_login_allowed'] === false) {
                location.href = this.logoutUrl;
            }
        }
    });
});
