/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/form/element/single-checkbox'
], function (_, checkbox) {
    'use strict';

    return checkbox.extend({
        defaults: {
            modules: {
                applicablePaymentsField: '${ $.applicablePaymentsFieldName }',
                paymentsField: '${ $.paymentsFieldName }'
            },
            applicablePaymentMethods: {
                allEnabled: '',
                specific: ''
            }
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();

            _.defer(this._checkStatus.bind(this));
        },

        /**
         * @inheritdoc
         */
        _checkStatus: function () {
            if (parseInt(this.value(), 10)) {
                this.disableDependencies();
            }
        },

        /**
         * @inheritdoc
         */
        disableDependencies: function () {
            this.applicablePaymentsField().disable();

            if (this.paymentsField()) {
                this.paymentsField().disable();
            }
        },

        /**
         * @inheritdoc
         */
        onCheckedChanged: function (newChecked) {
            this._super();

            if (this.applicablePaymentsField()) {
                if (!newChecked) {
                    this.applicablePaymentsField().enable();

                    if (this.applicablePaymentsField().value() === this.applicablePaymentMethods.specific) { //eslint-disable-line
                        this.paymentsField().enable();
                    }
                } else {
                    this.disableDependencies();
                }
            }
        }
    });
});
