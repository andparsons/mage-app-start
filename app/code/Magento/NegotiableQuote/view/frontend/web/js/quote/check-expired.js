/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/template',
    'text!Magento_NegotiableQuote/template/quote/expired-form.html',
    'mage/translate'
],
function ($, modal, mageTemplate, formTpl) {
    'use strict';

    $.widget('mage.checkExpired', {
        options: {
            isExpired: true,
            quoteButtonsCheckoutClass: '.quote-view-buttons .checkout',
            checkoutData: '',
            checkoutUrl: '',
            removeNegotiationUrl: null,
            dataForm: {},
            popup: {
                message: $.mage.__('If you proceed, all previously negotiated ' +
                'discounts will be removed from this quote.'),
                settings: {
                    type: 'popup',
                    title: $.mage.__('Go to Checkout?'),
                    modalClass: 'popup-expired-quote popup-tree',
                    responsive: true,
                    innerScroll: true,
                    buttons: ''
                }
            }
        },

        /**
         * init
         * @private
         */
        _init: function () {

            if (this.options.isExpired) {
                this.options.checkoutData = $(this.options.quoteButtonsCheckoutClass).data('post');
                this.options.checkoutUrl = this.options.checkoutData.action;
                $(this.options.quoteButtonsCheckoutClass).removeAttr('data-post');
                this._handleModal();
                this._handleButtonEvent();
            }
        },

        /**
         * handles modal settings and template
         * @private
         */
        _handleModal: function () {
            this.options.dataForm.message = this.options.popup.message;
            this.options.popup.settings.buttons = [{
                class: 'action save primary',
                type: 'button',
                text: $.mage.__('Proceed'),
                click: $.proxy(function () {
                    this._sendData();
                }, this)
            },{
                class: 'action cancel secondary',
                type: 'button',
                text: $.mage.__('Cancel'),

                /** Click action */
                click: function () {
                    this.closeModal();
                }
            }];

            this.form = $(mageTemplate(formTpl)({
                data: this.options.dataForm
            }));
            this.form = $(this.form[this.form.length - 1]);
            this.form.modal(this.options.popup.settings);
        },

        /**
         * handles button for calling modal
         * @private
         */
        _handleButtonEvent: function () {
            $(this.options.quoteButtonsCheckoutClass).on('click', $.proxy(function (e) {
                e.preventDefault();
                this.form.modal('openModal');
            }, this));
        },

        /**
         * sending data on modal accept
         * @private
         */
        _sendData: function () {
            this.form.modal('closeModal');

            $.ajax({
                url: this.options.removeNegotiationUrl,
                data:  {
                    'quote_id': this.options.checkoutData.data['quote_id']
                },
                showLoader: true,
                type: 'POST',
                success: $.proxy(function () {
                    location.replace(this.options.checkoutUrl);
                }, this)
            });
        }
    });

    return $.mage.checkExpired;
});
