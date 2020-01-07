/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/template',
    'text!Magento_NegotiableQuote/template/quote/notification-modal.html',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, mageTemplate, modalTpl) {
    'use strict';

    $.widget('mage.notificationModal', {
        options: {
            text: '',
            title: '',
            modalOptions: null
        },

        /**
         * Build widget
         *
         * @private
         */
        _create: function () {
            this._setModal();
            this._bind();
        },

        /**
         * Bind events
         *
         * @private
         */
        _bind: function () {
            this.element.on('notification', $.proxy(this._showModal, this));
        },

        /**
         * Set notification modal.
         *
         * @private
         */
        _setModal: function () {
            var popupOptions = {
                'type': 'popup',
                'modalClass': 'restriction-modal-quote',
                'responsive': true,
                'innerScroll': true,
                'title': $.mage.__(this.options.title),
                'buttons': [{
                    class: 'action-primary confirm action-accept',
                    type: 'button',
                    text: 'Ok',

                    /** Click action */
                    click: function () {
                        this.closeModal();
                    }
                }]
            };

            this.options.modalOptions = this.options.modalOptions || popupOptions;
            this.modalBlock = $(mageTemplate(modalTpl)({
                data: this.options.text
            }));
            this.modalBlock = this.modalBlock[this.modalBlock.length - 1];
            $(this.modalBlock).modal(this.options.modalOptions);
        },

        /**
         * Open notification modal.
         *
         * @private
         */
        _showModal: function () {
            $(this.modalBlock).modal('openModal');
        }
    });

    return $.mage.notificationModal;
});
