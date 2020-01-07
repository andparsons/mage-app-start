/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal-component',
    'mage/template',
    'text!Magento_CompanyCredit/template/credit-balance.html'
], function ($, Element, mageTpl, creditBalanceTpl) {
    'use strict';

    return Element.extend({
        defaults: {
            isAjax: false,
            modules: {
                historyTable: '${ $.historyTable }',
                htmlContent: '${ $.htmlContent }',
                reimburseBalance: '${ $.reimburseBalance }'
            },
            reimburseButton: '.reimburse-button',
            buttonTitle: {
                onSave: $.mage.__('Reimburse'),
                onEdit: $.mage.__('Save')
            }
        },

        /**
         * Init ui component
         *
         * @returns {Element}
         */
        initialize: function () {
            this.creditBalanceBlock = mageTpl(creditBalanceTpl);

            return this._super();
        },

        /**
         * Open modal
         *
         * @param {Object} data
         * @public
         */
        openModal: function (data) {
            var $reimburseButton = $(this.reimburseButton);

            if (typeof data === 'object') {
                this.data = data;
                this.url = this.data.url;

                if (this.data.title) {
                    this.setTitle(this.data.title);
                    $reimburseButton.text(this.buttonTitle.onSave);
                } else {
                    this.setTitle(this.options.title);
                    $reimburseButton.text(this.buttonTitle.onEdit);
                }

                this._resetFields()
                    ._setFields();
            }

            this._super();
        },

        /**
         * Close modal
         *
         * @public
         */
        closeModal: function () {
            this.reimburseBalance().elems().forEach(function (el) {
                el.disable();
            }, this);

            this._super();
        },

        /**
         * Set data to fields of modal
         *
         * @returns {Object} this
         * @private
         */
        _setFields: function () {
            var value;

            if (this.data.item) {
                this.reimburseBalance().elems().forEach(function (el) {
                    value = this.data.item[el.index + '_original'] || this.data.item[el.index];
                    el.source.set(el.dataScope, value);
                }, this);
            }

            return this;
        },

        /**
         * Reset and clear fields of modal
         *
         * @returns {Object} this
         * @private
         */
        _resetFields: function () {
            this.clear();
            this.reimburseBalance().elems().forEach(function (el) {
                el.enable()
                    .reset();
            }, this);

            return this;
        },

        /**
         * Validate everything validatable in modal
         *
         * @param {Object} elem
         * @public
         */
        validate: function (elem) {
            if (elem) {
                this._super();
            }
        },

        /**
         * Send Ajax
         *
         * @public
         */
        sendAjax: function () {
            var dataSend = {};

            if (this.data && this.data.item) {
                dataSend['history_id'] = this.data.item['entity_id'];
            }

            this.valid = true;
            this.reimburseBalance().elems().forEach(this.validate, this);

            if (this.valid && !this.isAjax) {
                this.isAjax = true;

                $.ajax({
                    url: this.url,
                    data: $.extend(dataSend, this.source.get(this.dataScope)),
                    type: 'post',
                    dataType: 'json',
                    showLoader: true,

                    /**
                     * @callback
                     */
                    success: $.proxy(function (data) {
                        if (this.historyTable()) {
                            this.historyTable().reload({
                                refresh: true
                            });
                        }

                        if (typeof data.balance != 'undefined') {
                            this.htmlContent().updateContent(this.creditBalanceBlock({
                                data: $.extend(data.balance, this.translate)
                            }));
                        }
                        this.isAjax = false;
                    }, this)

                    /**
                     * @callback
                     */
                });
                this.closeModal();
            }
        }
    });
});
