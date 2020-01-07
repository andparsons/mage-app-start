/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/confirm',
    'mage/template',
    'underscore',
    'jquery',
    'uiLayout',
    'mage/translate'
], function (SelectElement, confirm, mageTemplate, _, $) {
    'use strict';

    return SelectElement.extend({
        defaults: {
            confirmation: {
                contentTemplate: '<h1><%- header %></h1><div><%- message %></div>',
                text: {
                    header: '',
                    message: ''
                }
            }
        },

        /**
         * Callback that fires when 'value' property is updated.
         */
        onUpdate: function () {
            this._super();

            if (this.initialValue != this.value()) { //eslint-disable-line eqeqeq
                this._confirmChanges();
            }
        },

        /**
         * Cancel changes
         *
         * @private
         */
        _cancelChanges: function () {
            this.reset();
        },

        /**
         * Show confirmation popup
         *
         * @private
         */
        _confirmChanges: function () {
            confirm({
                modalClass: 'confirm confirm-customer-group-change',
                content: this._getConfirmationContent(),
                actions: {
                    cancel: _.bind(this._cancelChanges, this)
                },
                buttons: [{
                    text: $.mage.__('Cancel'),
                    'class': 'action-secondary action-dismiss',

                    /** @inheritdoc */
                    click: function (event) {
                        this.closeModal(event);
                    }
                }, {
                    text: $.mage.__('Proceed'),
                    'class': 'action-primary action-accept',

                    /** @inheritdoc */
                    click: function (event) {
                        this.closeModal(event, true);
                    }
                }]
            });
        },

        /**
         * Get confirmation popup content
         *
         * @returns {String}
         * @private
         */
        _getConfirmationContent: function () {
            return mageTemplate(this.confirmation.contentTemplate, this._getConfirmationTemplateParams());
        },

        /**
         * Get confirmation template params
         *
         * @returns {Object}
         * @protected
         */
        _getConfirmationTemplateParams: function () {
            return this.confirmation.text;
        }
    });
});
