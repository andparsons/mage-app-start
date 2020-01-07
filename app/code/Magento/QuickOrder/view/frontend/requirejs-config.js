/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            quickOrderMultipleSkus: 'Magento_QuickOrder/js/multiple-skus',
            quickOrderFile: 'Magento_QuickOrder/js/file',
            productSkuItem: 'Magento_QuickOrder/js/product-sku-item',
            countingErrors: 'Magento_QuickOrder/js/counting-errors',
            quickOrderAddToCart: 'Magento_QuickOrder/js/add-to-cart',
            quickOrderItemTable: 'Magento_QuickOrder/js/item-table'
        }
    },
    config: {
        mixins: {
            'mage/menu': {
                'Magento_QuickOrder/js/mage/menu': true
            }
        }
    }
};
