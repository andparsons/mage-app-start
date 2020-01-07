/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_SharedCatalog/js/wizard/step/pricing/category/tree/widget',
    'mage/translate'
], function ($, _, TreeWidget) {
    'use strict';

    $.widget('mage.sharedCatalogStateCategory', TreeWidget, {
        options: {
            buttons: {
                expandSelector: '[data-action="expand-state-tree"]'
            }
        }
    });

    return $.mage.sharedCatalogStateCategory;
});
