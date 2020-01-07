/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/lib/view/utils/async',
    'Magento_Ui/js/form/element/multiselect'
], function (_, $, Multiselect) {
    'use strict';

    return Multiselect.extend({
        defaults: {
            selector: 'select',
            modules: {
                applicablePaymentsField: '${ $.applicablePaymentsFieldName }'
            },
            applicablePaymentMethods: {
                specific: ''
            }
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();

            _.bindAll(this, '_disablePaymentMethodsField');

            $.async({
                component: this.applicablePaymentsField,
                selector: this.selector
            }, this._disablePaymentMethodsField);

            return this;
        },

        /**
         * Disable payment methods field if selected option is not 'Specific Payment Methods'
         *
         * @private
         */
        _disablePaymentMethodsField: function () {
            if (this.applicablePaymentsField().value() !== this.applicablePaymentMethods.specific) {
                this.disable();
            }
        }
    });
});
