/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract',
    'mage/template'
], function (CheckboxElement) {
    'use strict';

    return CheckboxElement.extend({
        defaults: {
            elementTmpl: 'Magento_Company/form/element/input'
        },

        /**
         * @returns {Element}
         */
        initialize: function () {
            return this._super()
                .initStateConfig();
        },

        /**
         * @returns {Element}
         */
        initStateConfig: function () {
            var companyAttrs;

            if (this.source) {
                companyAttrs = this.source.get(this.parentScope);

                this.selectedCompany = companyAttrs['company_name'];
                this.disabled(companyAttrs['is_super_user']);
            }

            return this;
        }
    });
});
