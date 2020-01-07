/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Customer/js/customer-data',
    'mage/dataPost',
    'mage/translate'
], function ($, modal, customerData, dataPost) {
    'use strict';

    $.widget('mage.createQuotePopup', {
        options: {
            popupTitle: $.mage.__('The shopping cart isn\'t empty'),
            cacheStorageName: 'mage-cache-storage',
            postData: '',
            mergeLink: 'replace_cart/1',
            popupSelector: '[data-role="reorder-quote-popup"]',
            buttonReorderSelector: '.action.order, .action.reorder',
            popupMainText: $.mage.__('You have items in your shopping cart. Would you like to merge items in this order with items of this shopping cart or replace them?'), //eslint-disable-line max-len
            popupSecondaryText: $.mage.__('Select Cancel to stay on the current page.'),
            mainTextSelector: '.text-main',
            secondaryTextSelector: '.text-secondary',
            buttonMergeSelector: '.action.merge',
            buttonReplaceSelector: '.action.replace',
            buttonCancelSelector: '.action.cancel'
        },

        /**
         *
         * @private
         */
        _create: function () {
            var self = this,
                options = {
                    'type': 'popup',
                    'modalClass': 'popup-reorder-quote',
                    'responsive': true,
                    'innerScroll': true,
                    'title': this.options.popupTitle,
                    'buttons': []
                };

            $(this.element).modal(options);
            $(this.options.popupSelector + ' ' +
            this.options.mainTextSelector).text(this.options.popupMainText);
            $(this.options.popupSelector + ' ' +
            this.options.secondaryTextSelector).text(this.options.popupSecondaryText);
            $(this.options.buttonReorderSelector).off('click');
            $(this.options.buttonReorderSelector).on('click', function (event) {
                event.stopImmediatePropagation();
                event.preventDefault();
                self.options.postData = $(this).data('post');

                if (self._checkCart()) {
                    self._doPopup();
                } else {
                    customerData.invalidate(['cart']);
                    self._runReorder(self.options.postData, '');
                }
            });
        },

        /**
         *
         * @private
         */
        _checkCart: function () {
            var cacheStorage = localStorage.getItem(this.options.cacheStorageName);

            cacheStorage = JSON.parse(cacheStorage);

            return cacheStorage.cart['summary_count'] > 0;
        },

        /**
         *
         * @private
         */
        _doPopup: function () {
            $(this.element).modal('openModal');

            $(this.options.buttonMergeSelector).on('click', $.proxy(function () {
                customerData.invalidate(['cart']);
                this._runReorder(this.options.postData, '');
            }, this));
            $(this.options.buttonReplaceSelector).on('click', $.proxy(function () {
                customerData.invalidate(['cart']);
                this._runReorder(this.options.postData, this.options.mergeLink);
            }, this));
            $(this.options.buttonCancelSelector).on('click', $.proxy(function () {
                $(this.element).modal('closeModal');
                event.preventDefault();
            }, this));
        },

        /**
         *
         * @param {Object} data
         * @param {String} linkArguments
         * @private
         */
        _runReorder: function (data, linkArguments) {
            var params = data;

            params.action += linkArguments;
            dataPost().postData(params);
        }
    });

    return $.mage.createQuotePopup;
});
