/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/validation',
    'Magento_Ui/js/modal/modal',
    'mage/mage'
], function ($) {
    'use strict';

    $.widget('mage.editAddress', {
        options: {
            isAjax: false,
            popupSelector: '#edit-address-popup',
            formSelector: '#add-address-form',
            modalClass: '',
            popupTitle: 'Change Address',
            closeButton: '.cancel-edit-address',
            addressList: '[name="quote_address"]',
            addressBox: '.box-shipping-address',
            addressId: 0,
            quoteId: 0,
            editAddressButton: '[data-role="edit-address"]',
            msg: '',
            updateUrl: ''
        },

        /**
         *
         * @private
         */
        _create: function () {
            var self = this,
                options = {
                'type': 'popup',
                'modalClass': 'popup-edit-address',
                'responsive': true,
                'innerScroll': true,
                'title': this.options.popupTitle,
                'buttons': [{
                    text: $.mage.__('Save'),
                    class: 'action save-and-get-address primary',
                    attr: {
                        'data-action': 'get-address',
                        'type': 'submit'
                    },

                    /** Click action */
                    click: function (e) {
                        e.preventDefault();

                        if (self.options.addressId != $(self.options.addressList).val()) { //eslint-disable-line
                            self.options.addressId = $(self.options.addressList).val();
                            self.editAddress();
                        } else {
                            this.closeModal();
                        }
                    }
                }, {
                    text: $.mage.__('Cancel'),
                    class: 'cancel-edit-address action secondary',

                    /** Click action */
                    click: function () {
                        this.closeModal();
                    }
                }]
            };

            this._bind();
            this.options.modalClass = options.modalClass;
            $(this.element).modal(options);
        },

        /**
         *
         * @private
         */
        _bind: function () {
            $(this.options.editAddressButton).on('click', $.proxy(function (e) {
                e.preventDefault();
                this.showModal();
            }, this));
            $(this.options.closeButton).on('click', $.proxy(function (e) {
                e.preventDefault();
                this.closeModal();
            }, this));
        },

        /**
         * Open modal dialog
         * @public
         */
        showModal: function () {
            $(this.element).modal('openModal');
            $('.' + this.options.modalClass + ' .modal-text').text($.mage.__(this.options.msg));
        },

        /**
         * Edit address in form.
         * @public
         */
        editAddress: function () {
            if (!this.options.isAjax) {
                this.options.isAjax = true;

                $.ajax({
                    url: this.options.updateUrl,
                    data: {
                        'quote_id': this.options.quoteId,
                        'address_id': this.options.addressId
                    },
                    type: 'post',
                    dataType: 'json',
                    showLoader: true,
                    context: $(this.options.popupSelector),

                    success: $.proxy(function (res) {
                        if (res.status !== 'error') {
                            location.reload();
                        }

                        this.closeModal();
                        this.options.isAjax = false;
                    }, this),

                    error: $.proxy(function () {
                        this.closeModal();
                        this.options.isAjax = false;
                    }, this)
                });
            }
        },

        /**
         * Close popup modal.
         * @public
         */
        closeModal: function () {
            $(this.element).modal('closeModal');
        }
    });

    return $.mage.editAddress;
});

