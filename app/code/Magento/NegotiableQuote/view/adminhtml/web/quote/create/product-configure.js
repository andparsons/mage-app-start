/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global Product*/
define([
    'Magento_ConfigurableProduct/js/configurable',
    'Magento_NegotiableQuote/catalog/product/composite/configure'
], function () {
    'use strict';

    return function (param) {
        /**
         * Set product configure
         */
        if (window.productConfigure) {
            param.config.containerId = window.productConfigure.blockFormFields.id;

            if (window.productConfigure.restorePhase) {
                param.config.inputsInitialized = true;
            }
        }

        window.ProductConfigure.spConfig = new Product.Config(param.config);
    };
});
