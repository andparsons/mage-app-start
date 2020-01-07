/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_SharedCatalog/js/store/switcher',
    'jquery',
    'underscore',
    'mage/translate'
], function (StoreSwitcher) {
    'use strict';

    return StoreSwitcher.extend({
        defaults: {
            modules: {
                treeProvider: '${ $.treeProvider }',
                structureStoreSwitcher: '${ $.structureStoreSwitcher }'
            },
            imports: {
                selectedStore: '${ $.structureStoreSwitcher }:selectedStore'
            },
            exports: {
                selectedStore: '${ $.treeProvider }:params.filters.store'
            }
        }
    });
});
