/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            jsTreeWidget: 'Magento_SharedCatalog/js/jstree/widget',
            jsTreeNew: 'Magento_SharedCatalog/js/jstree/jstree',
            redirectionUrl: 'mage/redirect-url'
        }
    },
    config: {
        mixins: {
            'mage/validation': {
                'Magento_SharedCatalog/js/validation': true
            }
        }
    }
};
