/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            roleTree: 'Magento_Company/js/role-tree',
            hierarchyTree: 'Magento_Company/js/hierarchy-tree',
            hierarchyTreePopup: 'Magento_Company/js/hierarchy-tree-popup'
        }
    },
    config: {
        mixins: {
            'mage/validation': {
                'Magento_Company/js/validation': true
            }
        }
    }
};
