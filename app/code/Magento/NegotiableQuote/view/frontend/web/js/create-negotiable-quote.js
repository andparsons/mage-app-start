/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/customer-data',
    'mage/template',
    'text!Magento_NegotiableQuote/template/request-modal.html',
    'Magento_NegotiableQuote/js/model/create-quote-popup',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, _, quote, customerData, mageTemplate, modalTpl) {
    'use strict';

    $.widget('mage.createNegotiableQuote', {
        options: {
            url: '',
            isAjax: false,
            discount: false,
            popupSelector: '#negotiable-quote-popup',
            formSelector: '[data-role="negotiable-quote-popup"]',
            quoteNameSelector: '#quote-name',
            quoteMessageSelector: '#quote-message',
            summaryCityRegionSelector: 'select[name=country_id]',
            summaryCityIdSelector: 'select[name=region_id]',
            summaryPostcodeSelector: 'input[name=postcode]',
            summaryRegionSelector: 'input[name=region]',
            popupTitles: {
                quoteRules: $.mage.__('Quote request rules'),
                quoteRequest: $.mage.__('Request a Quote')
            },
            popup: '',
            closeButton: '.cancel-quote-request',
            saveButton: '[data-action="save-quote"]',
            urlPost: 'quote_id/',
            moveUrl: '',
            negotiableFormButtonSelector: '#negotiable-quote-form button',
            attachedFiles: '[data-role="add-file"]',
            form: '[data-action="comment-form"]',
            checkoutBtn: '[data-role="proceed-to-checkout"]',
            quoteRulesModalText: [
                $.mage.__('Coupon codes and gift cards are not allowed on quotes. Choose whether you\'d like to keep the coupon code/gift card without requesting a quote, or request a quote without the coupon code/gift card.') //eslint-disable-line max-len
            ]
        },

        /**
         *
         * @private
         */
        _create: function () {
            var options;

            this.modalBlockTmpl = mageTemplate(modalTpl);
            this._setRequestModal();

            options = {
                'type': 'popup',
                'modalClass': 'popup-request-quote',
                'focus': '[data-role="negotiable-quote-popup"] .textarea',
                'responsive': true,
                'innerScroll': true,
                'title': this.options.popupTitles.quoteRequest,
                'buttons': []
            };

            this.options.moveUrl = this.options.moveUrl + this.options.urlPost;
            this._bind();
            $(this.element).modal(options);
        },

        /**
         *
         * @private
         */
        _bind: function () {
            $(this.options.negotiableFormButtonSelector).on('click', $.proxy(function () {
                this.showModal();
            }, this));
            $(this.options.closeButton).on('click', $.proxy(function (e) {
                e.preventDefault();
                this.closeModal();
            }, this));
            $(this.options.formSelector).on('submit', $.proxy(function (e) {
                e.preventDefault();
                this.ajaxPOST();
            }, this));
        },

        /**
         * @private
         */
        _setRequestModal: function () {
            var self = this,
                options;

            this.options.popup = _.last($(this.modalBlockTmpl({
                data: this.options.quoteRulesModalText
            })));

            options = {
                'type': 'popup',
                'modalClass': 'popup-request-quote-discounts popup-request-rules',
                'responsive': true,
                'innerScroll': true,
                'title': this.options.popupTitles.quoteRules,
                'buttons': [{
                    text: $.mage.__('Go to Checkout'),

                    /** Click action */
                    click: function () {
                        this.closeModal();
                        $(self.options.checkoutBtn).click();
                    },
                    'class': 'action primary'
                },
                {
                    text: $.mage.__('Request a Quote'),

                    /** Click action */
                    click: function () {
                        this.closeModal();
                        $(self.element).modal('openModal');
                    }
                },
                {
                    text: $.mage.__('Cancel'),

                    /** Click action */
                    click: function () {
                        this.closeModal();
                    },
                    'class': 'action cancel'
                }]
            };

            $(this.options.popup).modal(options);
        },

        /**
         *
         * @private
         */
        showModal: function () {
            this._checkDiscount();
        },

        /**
         *
         * @private
         */
        closeModal: function () {
            $(this.element).modal('closeModal');
        },

        /**
         *
         * @private
         */
        _checkDiscount: function () {
            if (!this.options.isAjax) {
                this.options.isAjax = true;

                $.ajax({
                    url: this.options.url,
                    data: {
                        'quote_id': quote.getQuoteId()
                    },
                    type: 'get',
                    dataType: 'json',
                    showLoader: true,
                    success: $.proxy(function (res) {
                        if (res && res.data && res.data.discount) {
                            $(this.options.popup).modal('openModal');
                        } else {
                            $(this.element).modal('openModal');
                        }

                        this.options.isAjax = false;
                    }, this),
                    error: $.proxy(function () {
                        this.options.isAjax = false;
                    }, this)
                });
            }
        },

        /**
         * Retrieve form data
         *
         * @returns {FormData}
         * @private
         */
        _getFormData: function () {
            return new FormData($(this.options.form)[0]);
        },

        /**
         *
         * @private
         */
        ajaxPOST: function () {
            var formData = this._getFormData();

            if ($(this.options.form).valid()) {
                this.options.isAjax = true;

                $.ajax({
                    url: this.options.moveUrl + quote.getQuoteId(),
                    data: formData,
                    contentType: false,
                    processData: false,
                    type: 'post',
                    dataType: 'json',
                    showLoader: true,
                    context: $(this.options.formSelector),
                    success: $.proxy(function (res) {
                        $(this.options.popupSelector).modal('closeModal');
                        this.options.isAjax = false;

                        if (res.status !== 'error') {
                            customerData.invalidate(['cart']);
                        }

                        if (res.data && res.data.url) {
                            this.moveTo(res.data.url);
                        }
                    }, this),
                    error: $.proxy(function () {
                        this.options.isAjax = false;
                    }, this)
                });
            }
        },

        /**
         *
         * @private
         */
        moveTo: function (url) {
            location.href = url;
        }
    });

    return $.mage.createNegotiableQuote;
});
