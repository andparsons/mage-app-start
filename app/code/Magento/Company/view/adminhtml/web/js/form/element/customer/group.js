/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_B2b/js/form/element/ui-group'
], function (SelectElement) {
    'use strict';

    return SelectElement.extend({
        defaults: {
            companyIdPath: 'data.customer.extension_attributes.company_attributes.company_id'
        },

        /**
         * Initialize
         *
         * @returns {Element}
         */
        initialize: function () {
            return this
                ._super()
                .initStateConfig();
        },

        /**
         * Initialize config
         *
         * @returns {Element}
         */
        initStateConfig: function () {
            if (this.source.get(this.companyIdPath)) {
                this.disabled(true);
            }

            return this;
        }
    });
});
