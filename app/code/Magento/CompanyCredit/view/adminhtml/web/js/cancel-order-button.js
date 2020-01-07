/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'text!Magento_CompanyCredit/template/cancel-order-form.html',
    'mage/template',
    'mage/translate'
], function ($, confirm, formTpl, mageTpl) {
    'use strict';

    $.widget('mage.cancelOrder', {

        options: {
            title: $.mage.__('Cancel the Order'),
            message: '',
            url: ''
        },

        /**
         * Build widget
         *
         * @private
         */
        _create: function () {
            this._bind();
        },

        /**
         * Bind events
         *
         * @private
         */
        _bind: function () {
            this._unbind();
            this.element.on('click',  $.proxy(this._showConfirm, this));
        },

        /**
         * Create form
         *
         * @returns {jQuery}
         */
        _createForm: function () {
            var data;

            this.formBlock = mageTpl(formTpl);
            data = {
                'url': this.options.url,
                'key': window.FORM_KEY
            };

            return $(this.formBlock({
                data: data
            }));
        },

        /**
         * Show confirmation popup
         *
         * @private
         */
        _showConfirm: function () {
            var self = this;

            confirm({
                'title': this.options.title,
                'content': this.options.message,
                'actions': {

                    /**
                     * 'Confirm' action handler.
                     */
                    confirm: function () {
                        self._createForm().appendTo('body').submit();
                    }
                }
            });
        },

        /**
         * Unbind previous handler with standard confirmation popup
         *
         * @private
         */
        _unbind: function () {
            this.element.off('click');
        }
    });

    return $.mage.cancelOrder;
});
