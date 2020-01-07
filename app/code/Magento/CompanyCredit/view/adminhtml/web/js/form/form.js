/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/lib/spinner',
    'Magento_Ui/js/form/form'
], function (loader, Form) {
    'use strict';

    return Form.extend({

        /**
         * Show loader.
         *
         * @returns {Object}
         */
        showLoader: function () {
            loader.get(this.name).show();

            return this;
        },

        /**
         * Submits form with loader
         *
         * @param {String} redirect
         */
        submit: function (redirect) {
            this.showLoader();
            this._super(redirect);
        }
    });
});
