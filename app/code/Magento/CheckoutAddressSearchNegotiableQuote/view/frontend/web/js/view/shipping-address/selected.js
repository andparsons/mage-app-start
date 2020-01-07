/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_CheckoutAddressSearch/js/view/shipping-address/selected',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/shipping-rate-service'
], function (_, Component, addressList, selectShippingAddressAction, checkoutData) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initObservable: function () {
            var checkoutConfig = window.checkoutConfig,
                defaultShippingKey = checkoutConfig.selectedShippingKey;

            this._super();

            this.hasQuoteAddress = checkoutConfig.selectedShippingKey &&
                checkoutConfig.isAddressSelected &&
                checkoutConfig.isNegotiableQuote;

            if (this.hasQuoteAddress) {
                _.each(addressList(), function (address) {
                    if (address.getKey() === defaultShippingKey) {
                        selectShippingAddressAction(address);
                        checkoutData.setSelectedShippingAddress(address.getKey());
                    }
                }, this);
            }

            return this;
        }
    });
});
