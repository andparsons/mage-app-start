/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    config: {
        mixins: {
            'mage/validation': {
                'Magento_CompanyCredit/js/validation': true
            },
            'Magento_Tax/js/view/checkout/summary/grand-total': {
                'Magento_CompanyCredit/js/view/checkout/summary/grand-total': true
            }
        }
    }
};
