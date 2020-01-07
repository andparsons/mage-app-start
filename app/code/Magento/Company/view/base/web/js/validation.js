/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/lib/validation/validator',
    'mage/translate'
], function (validator, $t) {
    'use strict';

    return function (target) {
        validator.addRule(
            'validate-customer-company',
            function (value, params, data) {
                return !(data.customer && data['is_company_user']);
            },
            $t('This customer is a user of a different company. Enter a different email address to continue.')
        );

        validator.addRule(
            'validate-customer-status',
            function (value, params, data) {
                return !(data.customer && !data['is_active']);
            },
            $t('The selected user is inactive. To continue, select another user or activate the current user.')
        );

        validator.addRule(
            'validate-async-company-email',
            function (value, params, data) {
                return data['is_company_email_available'];
            },
            $t('Company with this email address already exists in the system. Enter a different email address' +
                ' to continue.')
        );

        return target;
    };
});
