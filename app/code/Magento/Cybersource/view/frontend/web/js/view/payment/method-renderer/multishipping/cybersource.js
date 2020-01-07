/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Cybersource/js/view/payment/method-renderer/cybersource',
    'Magento_Checkout/js/action/set-payment-information-extended'
], function ($, Component, setPaymentInformationExtended) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Cybersource/payment/multishipping/cybersource-form'
        },

        /**
         * Overrides parent method to save the payment information without billing address.
         *
         * @override
         */
        setPaymentInformation: function () {
            return setPaymentInformationExtended(
                this.messageContainer,
                {
                    method: this.getCode()
                },
                true
            );
        }
    });
});
