/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'mage/template',
    'text!Magento_NegotiableQuote/template/quote/add-file.html',
    'text!Magento_NegotiableQuote/template/quote/attentional-modal.html',
    'text!Magento_NegotiableQuote/template/quote/attentional-text.html',
    'text!Magento_NegotiableQuote/template/quote/error.html',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'Magento_NegotiableQuote/js/quote/add-file',
    'Magento_NegotiableQuote/js/quote/delete-files'
], function ($, _, mageTemplate, fileTpl, modalTpl, textTpl, errorTpl) {
    'use strict';

    $.widget('mage.addFiles', {
        options: {
            wrapperAttach: '[data-role="attach-wrapper"]',
            wrapFiles: '[data-role="history-added-files"]',
            errorBlock: '[data-role="error"]',
            errorWrap: '[data-role="error-wrap"]',
            attachFile: '[data-role="attached-item"]',
            errorTextMaxAmount: $.mage.__('You cannot attach more than ten files per comment.'),
            errorTextMaxSize: '',
            labelText: $.mage.__('Attach file'),
            popupTitle: $.mage.__('Cannot Upload File'),
            maxFiles: 10,
            amountFiles: 0,
            maxNameLength: 20,
            idFiles: 1,
            isError: false,
            fileIsHide: false,
            maxSizeB: '',
            maxSizeMb: '',
            extensions: '',
            validateFile: true,
            typeError: {},
            modal: false
        },

        /**
         * Create widget
         * @type {Object}
         */
        _create: function () {
            this.extensions = this.options.extensions.split(',');
            this.wrapper = $(this.options.wrapperAttach);
            this.errorWrapper = $(this.options.errorWrap);
            this.fileTemplate = mageTemplate(fileTpl);
            this.errorTemplate = mageTemplate(errorTpl);

            if (this.options.modal) {
                this.modalTemplate = mageTemplate(modalTpl);
                this._initModal();
            }

            this._bind();
        },

        /**
         * Add events on items.
         * @private
         */
        _bind: function () {
            $(this.element).on('change', $.proxy(this._getFiles, this));
            $(this.element).on('add', $.proxy(this._addFile, this));
            $(this.element).on('clear', $.proxy(this._clearError, this));
            $(this.element).on('error', $.proxy(this._addError, this));
        },

        /**
         * Add label for attached file
         * @private
         */
        _addFile: function (e, data) {
            this.options.fileIsHide = false;
            this._removeErrors();

            if (data) {
                this._validateFile(data);
            }
        },

        /**
         * Validate file
         * @private
         */
        _validateFile: function (data) {
            if (this._validateExtensions(data) || this._validateSize(data) || this._validateLengthName(data)) {
                this._delLastFile(data);
                this._renderFile();
                this._setErrorText();

                return false;
            }

            if (this._getAmountFiles() === this.options.maxFiles) {
                this.options.fileIsHide = true;
                this._delLastFile(data);

                if (this.options.modal) {
                    this._addError(false, this.options.errorTextMaxAmount);
                } else {
                    this._setErrorText(this.options.errorTextMaxAmount);
                }

                return false;
            }

            this._renderFile();
        },

        /**
         * Validate file on size
         * @private
         */
        _validateSize: function (data) {
            var maxSize  = parseFloat(this.options.maxSizeB);

            if (maxSize && data.data.size <= maxSize) {
                return false;
            }

            this.options.typeError =  {
                text: 'The maximum allowed file size is ' +
                    this.options.maxSizeMb +
                    ' MB. Please select a different file.',
                subText: false
            };

            return true;
        },

        /**
         * Validate file on length name
         * @private
         */
        _validateLengthName: function (data) {
            var checkSymbols = /^[^~`!@#$%^&*()+={}[\]|;:"',.?><\/\\\s]+[.]{1}[^~`!@#$%^&*()+={}[\]|;:"',.?><\/\\\s]+$/;

            if (data.data.name.length <= this.options.maxNameLength && checkSymbols.test(data.data.name)) {
                return false;
            }

            this.options.typeError =  {
                text: 'The maximum file name length is ' + this.options.maxNameLength + ' characters. ' +
                'The only special symbols that are allowed in the file name are dash (-) and underscore (_).',
                subText: false
            };

            return true;
        },

        /**
         * Validate file on type
         * @private
         */
        _validateExtensions: function (data) {
            var length = this.options.extensions.split(',').length,
                extensions = _.last(data.data.name.split('.')),
                i;

            for (i = 0; i <= length; i++) {
                if (extensions === this.extensions[i] || this.options.extensions === '') {
                    return false;
                }
            }

            this.options.typeError =  {
                text: extensions.toUpperCase() + ' is not an allowed file type. Please select a different file.',
                subText: 'Allowed file formats: ' + this.extensions.join(', ') + '.'
            };

            return true;
        },

        /**
         * Set error text on modal or wrapper
         * @private
         */
        _setErrorText: function (text) {
            if (text) {
                this.options.typeError.text = text;
            }

            this.attentionalText = _.last($(mageTemplate(textTpl)({
                data: $.mage.__(this.options.typeError)
            })));

            if (this.options.modal) {
                this.attentionalModal.html(this.attentionalText);
                this._openModal();
            } else {
                this.errorWrapper.html(this.attentionalText)
                    .show();
            }
        },

        /**
         * Init modal
         * @private
         */
        _initModal: function () {
            var modalOptions = {
                    'type': 'popup',
                    'modalClass': 'popup-attentional-quote-error main-popup',
                    'responsive': true,
                    'innerScroll': true,
                    'title': $.mage.__(this.options.popupTitle),
                    'buttons': [{
                        class: 'action-primary action-accept',
                        type: 'button',
                        text: 'Ok',

                        /** Click action */
                        click: function () {
                            this.closeModal();
                        }
                    }]
                };

            this.attentionalModal = $(_.last($(mageTemplate(modalTpl)())));
            this.attentionalModal.modal(modalOptions);
        },

        /**
         * Open modal
         * @private
         */
        _openModal: function () {
            this.attentionalModal.modal('openModal');
        },

        /**
         * Render label file
         * @private
         */
        _renderFile: function () {
            var fileData = {
                    text: this.options.labelText,
                    index: this.options.idFiles++
                },
                file = $(this.fileTemplate({
                    data:  fileData
                }));

            file.addFile();
            $(this.element).append(_.last(file));
        },

        /**
         * Remove last attached file
         * @private
         */
        _delLastFile: function (data) {
            data.element.remove();
            data.fileName.remove();
        },

        /**
         * Clear error message
         * @private
         */
        _clearError: function () {
            this._removeErrors();

            if (this.options.fileIsHide) {
                this.options.fileIsHide = false;
                this._renderFile();
            }
        },

        /**
         * Get amount attached files
         * @private
         */
        _getAmountFiles: function () {
            return this.wrapper.find(this.options.attachFile).length - 1;
        },

        /**
        * Add error on page
        * @private
        */
        _addError: function (e, text) {
            var textError = text,
                file;

            if (typeof textError === 'object') {
                textError = textError.text;
            }
            file = $(this.errorTemplate({
                data: textError
            }));
            this._removeErrors();
            $(this.options.wrapFiles).html(_.last(file));
        },

        /**
        * Remove error on page
        * @private
        */
        _removeErrors: function () {
            $(this.options.errorBlock).remove();
            this.errorWrapper.hide();
        }

    });

    return $.mage.addFiles;
});
