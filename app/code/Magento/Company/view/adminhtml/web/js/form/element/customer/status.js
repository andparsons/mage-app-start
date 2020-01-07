/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/single-checkbox',
    'mage/template'
], function (CheckboxElement, mageTemplate) {
    'use strict';

    return CheckboxElement.extend({
        defaults: {
            'superuser_config': {
                disabled: true
            },
            paths: {
                'is_super_user': 'data.customer.extension_attributes.company_attributes.is_super_user',
                'company_name': 'data.customer.extension_attributes.company_attributes.company_name',
                'first_name': 'data.customer.firstname',
                'last_name': 'data.customer.lastname'
            }
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
            if (this.source.get(this.paths['is_super_user'])) {
                this.notice = this._getSuperUserNotice();
                this.disabled(this['superuser_config'].disabled);
            }

            return this;
        },

        /**
         * Get super user notice
         * @returns {String}
         * @private
         */
        _getSuperUserNotice: function () {
            var data = {
                company: this.source.get(this.paths['company_name']),
                username: this.source.get(this.paths['first_name']) + ' ' + this.source.get(this.paths['last_name'])
            };

            return mageTemplate(this['superuser_config'].notice, data);
        }
    });
});
