/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['Magento_NegotiableQuote/catalog/product/composite/configure'], function () {
    'use strict';

    return function (params) {
        /**
         * Init error block
         */
        window.productConfigure.addListType(params.listType, {
            urlFetch: params.url
        });
    };
});
