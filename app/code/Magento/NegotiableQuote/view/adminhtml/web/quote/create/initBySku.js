/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global AddBySku */
define(['Magento_NegotiableQuote/quote/create/addbysku'], function () {
    'use strict';

    return function (conf) {
        /**
         * @param {*} data
         */
        function initSku(data) {
            window.addBySku = new AddBySku(data);
        }

        window.initSku = initSku;
        initSku(conf);
    };
});
