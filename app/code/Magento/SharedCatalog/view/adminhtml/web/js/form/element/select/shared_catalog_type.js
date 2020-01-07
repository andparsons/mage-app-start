/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_SharedCatalog/js/form/element/select/field_with_confirmation',
    'Magento_Ui/js/modal/confirm',
    'mage/template',
    'underscore',
    'jquery',
    'uiLayout',
    'mage/translate'
], function (SelectElement) {
    'use strict';

    return SelectElement.extend({
        defaults: {
            confirmation: {
                valueToConfirm: null,
                'text_create': {
                    header: '',
                    message: ''
                },
                'text_edit': {
                    header: '',
                    message: ''
                }
            }
        },

        /**
         * Callback that fires when 'value' property is updated.
         */
        onUpdate: function () {
            if (this.value() == this.confirmation.valueToConfirm) { //eslint-disable-line eqeqeq
                this._super();
            }
        },

        /**
         * Get confirmation template params
         *
         * @returns {Object}
         * @protected
         */
        _getConfirmationTemplateParams: function () {
            return this._isCreateForm() ?
                this.confirmation['text_create'] :
                this.confirmation['text_edit'];
        },

        /**
         * Is create form
         *
         * @returns {Boolean}
         * @private
         */
        _isCreateForm: function () {
            var sourceData = this.source.get('data');

            return !sourceData['shared_catalog_id'];
        }
    });
});
