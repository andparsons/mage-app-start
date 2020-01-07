/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'Magento_SalesRule/js/view/payment/discount',
    'Magento_SalesRule/js/action/set-coupon-code',
    'Magento_SalesRule/js/action/cancel-coupon'
], function (ko, DiscountView, setCouponCodeAction, cancelCouponAction) {
    'use strict';

    return DiscountView.extend({
        defaults: {
            template: 'Magento_NegotiableQuote/payment/discount'
        },

        /**
         * Applied flag
         */
        isLoading: ko.observable(false),
        isDisable: window.checkoutConfig.isDiscountFieldLocked && window.checkoutConfig.isNegotiableQuote,

        /**
         * Coupon code application procedure.
         */
        apply: function () {
            if (this.validate() && !this.isDisable) {
                setCouponCodeAction(this.couponCode(), this.isApplied, this.isLoading);
            }
        },

        /**
         * Cancel using coupon.
         */
        cancel: function () {
            if (this.validate()) {
                this.couponCode('');
                cancelCouponAction(this.isApplied, this.isLoading);
            }
        }
    });
});
