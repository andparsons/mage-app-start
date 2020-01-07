/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'underscore',
    'jquery',
    'mage/template',
    'text!Magento_NegotiableQuote/template/quote/form.html',
    'Magento_Ui/js/modal/modal',
    'jquery/validate',
    'mage/validation',
    'mage/translate'
], function (Component, _, $, mageTemplate, formTpl) {
    'use strict';

    return Component.extend({
        options: {
            eventType: 'click',
            popup: {
                'quote_decline_opened': {
                    title: $.mage.__('Decline Quote?'),
                    text: $.mage.__('If you click [Confirm], all custom pricing from the selected quotes will be removed. The buyers will be able to place orders using their standard catalog prices.') //eslint-disable-line max-len
                },
                'quote_decline_mixed': {
                    title: $.mage.__('Decline Quotes?'),
                    text: $.mage.__('You selected the quotes in various statuses. ' +
                    'Only the quotes in \'Open\' status will be declined.')
                },
                'quote_decline_deny': {
                    title: $.mage.__('Cannot Decline the Selected Quotes'),
                    text: $.mage.__('Only the quotes that are currently in status \'Open\' can be declined')
                },
                settings: {
                    'type': 'popup',
                    'modalClass': 'popup-tree-decline',
                    'responsive': true,
                    'innerScroll': true
                },
                type: '',
                textareaLabel: $.mage.__('Please specify the reason'),
                formDeclineMessageBlock: '[data-role="modal-decline-notice"]',
                formDeclineInputsBlock: '[data-role="modal-decline-inputs"]',
                formTextareaErrorBlock: '[data-role="modal-textarea-error"]',
                textareaErrorMessage: $.mage.__('This is a required field.')
            },
            selected: '',
            tableData: '',
            dataForm: {}
        },

        /**
         * Init widget
         * @public
         */
        initialize: function () {
            this._super();
            _.bindAll(this, 'decline');
        },

        /**
         * Decline callback
         *
         * @param {Object} action - Action data.
         * @param {Object} data - Selections data.
         */
        decline: function (action, data) {
            var itemsType = data.excludeMode ? 'excluded' : 'selected',
                selections = {};

            this.options.tableData = arguments;
            this.options.url = arguments[0].url;
            this.options.statusesUrl = arguments[0].validateUrl;

            selections[itemsType] = data[itemsType];

            if (!selections[itemsType].length) {
                selections[itemsType] = false;
            }

            _.extend(selections, data.params || {});

            this._requestStatus(selections);
        },

        /**
         * Start to check status of selected quotes
         * @private
         *
         * @param {Object} data
         */
        _requestStatus: function (data) {
            var self = this;

            this.options.dataForm.textarealabel = this.options.popup.textareaLabel;
            this.options.dataForm.messagenotice = this.options.popup['quote_decline_mixed'].text;
            this.options.dataForm.textareaerror = this.options.popup.textareaErrorMessage;

            $.ajax({
                url: self.options.statusesUrl,
                data: data,
                type: 'POST',

                /**
                 * @param {Object} response
                 */
                success: function (response) {
                    self._checkStatus(response.items, data);
                }
            });
        },

        /**
         * Validates form text field
         * @private
         *
         */
        _validateForm: function (data) {
            var $textarea = this.form.find(this.options.popup.formDeclineInputsBlock + ' textarea');

            if ($textarea.val().length === 0) {
                this.form.find(this.options.popup.formTextareaErrorBlock).show();
                $textarea.addClass('mage-error');
            } else {
                this.form.find(this.options.popup.formTextareaErrorBlock).hide();
                $textarea.removeClass('mage-error');
                this._sendForm(data);
            }
        },

        /**
         * Process statuses and determine which modal to show
         * @private
         *
         * @param {Array} response
         * @param {Object} data
         */
        _checkStatus: function (response, data) {
            var self = this;

            if (response.length < this.options.tableData[1].total && response.length !== 0) {
                this.options.popup.type = 'mixed';
                this.options.dataForm.message = this.options.popup['quote_decline_opened'].text;
                this.options.popup.settings.title = this.options.popup['quote_decline_mixed'].title;
                this.options.popup.settings.buttons = [{
                    class: 'action-primary cancel action-accept',
                    type: 'button',
                    text: $.mage.__('Cancel'),

                    /** Click action. */
                    click: function () {
                        this.closeModal();
                        self.form.validation('clearError');
                        self.form.modal('destroy');
                    }
                },{
                    class: 'action-primary confirm action-accept',
                    type: 'button',
                    text: $.mage.__('Confirm'),

                    /** Click action. */
                    click: function () {
                        self._validateForm(data);
                        self.form.modal('destroy');
                    }
                }];
            } else if (response.length === 0) {
                this.options.popup.type = 'noOpen';
                this.options.dataForm.message = this.options.popup['quote_decline_deny'].text;
                this.options.popup.settings.title = this.options.popup['quote_decline_deny'].title;
                this.options.popup.settings.buttons = [{
                    class: 'action-primary confirm action-accept',
                    type: 'button',
                    text: $.mage.__('OK'),

                    /** Click action. */
                    click: function () {
                        this.closeModal();
                        self.form.validation('clearError');
                        self.form.modal('destroy');
                    }
                }];
            } else if (response.length == this.options.tableData[1].total) { //eslint-disable-line eqeqeq
                this.options.popup.type = 'gotOpen';
                this.options.dataForm.message = this.options.popup['quote_decline_opened'].text;
                this.options.popup.settings.title = this.options.popup['quote_decline_opened'].title;
                this.options.popup.settings.buttons = [{
                    class: 'action-primary cancel action-accept',
                    type: 'button',
                    text: $.mage.__('Cancel'),

                    /** Click action. */
                    click: function () {
                        this.closeModal();
                        self.form.validation('clearError');
                        self.form.modal('destroy');
                    }
                },{
                    class: 'action-primary confirm action-accept',
                    type: 'button',
                    text: $.mage.__('Confirm'),

                    /** Click action. */
                    click: function () {
                        self._validateForm(data);
                        self.form.modal('destroy');
                    }
                }];

            }

            this._create();
            this._showModal();
        },

        /**
         * Show modal popup.
         * @private
         */
        _showModal: function () {
            this.form.modal('openModal');
        },

        /**
         * Build widget
         * @private
         */
        _create: function () {
            this.form = $(mageTemplate(formTpl)({
                data: this.options.dataForm
            }));
            this.form = $(this.form[this.form.length - 1]);
            this.form.validation();
            this.form.find(this.options.popup.formTextareaErrorBlock).hide();

            if (this.options.popup.type === 'gotOpen') {
                this.form.find(this.options.popup.formDeclineInputsBlock).show();
                this.form.find(this.options.popup.formDeclineMessageBlock).hide();
            } else if (this.options.popup.type === 'noOpen') {
                this.form.find(this.options.popup.formDeclineInputsBlock).hide();
                this.form.find(this.options.popup.formDeclineMessageBlock).hide();
            } else if (this.options.popup.type === 'mixed') {
                this.form.find(this.options.popup.formDeclineInputsBlock).show();
                this.form.find(this.options.popup.formDeclineMessageBlock).show();
            }

            this.form.modal(this.options.popup.settings);
        },

        /**
         * Send form
         * @private
         */
        _sendForm: function (data) {
            var self = this;

            data.declineMessage = this.form.find(self.options.popup.formDeclineInputsBlock + ' textarea').val();
            this.form.modal('closeModal');

            $.ajax({
                url: self.options.url,
                type: 'POST',
                data: data,
                showLoader: true,

                /** Success callback */
                success: function () {
                    window.location.reload(true);
                }
            });
        }
    });
});
