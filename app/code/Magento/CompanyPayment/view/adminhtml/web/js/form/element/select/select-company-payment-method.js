/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/form/element/select'
], function (_, SelectElement) {
    'use strict';

    return SelectElement.extend({
        defaults: {
            modules: {
                paymentsField: '${ $.paymentsFieldName }'
            },
            applicablePaymentMethods: {
                b2b: '',
                allEnabled: ''
            }
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            _.defer(this._selectOptions.bind(this));
        },

        /**
         * @inheritdoc
         */
        onUpdate: function () {
            this._super();
            this._selectOptions();
        },

        /**
         * Select options
         *
         * @private
         */
        _selectOptions: function () {
            if (this.value() === this.applicablePaymentMethods.b2b) {
                this._disablePaymentMethodsField();

                if (this.paymentsField()) {
                    this.paymentsField().value(this._getSelectedPaymentMethods());
                }
            } else if (this.value() === this.applicablePaymentMethods.allEnabled) {
                this._disablePaymentMethodsField();
                this.paymentsField().value(this._getInitialOptions());
            } else if (!this.disabled()) {
                this.paymentsField().enable();
            }
        },

        /**
         * Disable payment methods field
         *
         * @private
         */
        _disablePaymentMethodsField: function () {
            if (this.paymentsField() && !this.paymentsField().disabled()) {
                this.paymentsField().disable();
            }
        },

        /**
         * Get initial payment methods
         *
         * @returns {Array}
         * @private
         */
        _getInitialOptions: function () {
            var options = [],
                initialOptions = this.paymentsField().initialOptions,
                i;

            for (i = 0; i < initialOptions.length; i++) {
                options[i] = initialOptions[i].value;
            }

            return options;
        },

        /**
         * Get selected payment methods
         *
         * @returns {Array}
         * @private
         */
        _getSelectedPaymentMethods: function () {
            var selectedMethods;

            if (this.b2bPaymentMethods) {
                selectedMethods = this.b2bPaymentMethods.split(',');
            }

            return selectedMethods;
        }
    });
});
