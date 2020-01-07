/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/dataPost',
    'mage/translate',
    'mage/backend/notification'
], function ($, confirm, dataPost) {
    'use strict';

    $.widget('mage.postWrapper', {

        options: {
            url: '',
            text: $.mage.__('This action cannot be undone. Are you sure you want to delete this company? After the company is deleted, all the company members will be set to Inactive.'), //eslint-disable-line max-len
            title: $.mage.__('Delete a Company?')
        },

        /**
         * @private
         */
        _init: function () {
            var self = this;

            $(this.element).on('click', $.proxy(function () {
                confirm({
                    'content': this.options.text,
                    'title': this.options.title,
                    'modalClass': 'confirm delete-company',
                    'actions': {

                        /**
                         * 'Confirm' action handler.
                         */
                        confirm: function () {
                            $.ajax({
                                url: self.options.url,
                                data: {
                                    'form_key': window.FORM_KEY
                                },
                                type: 'POST',
                                dataType: 'json',
                                showLoader: true,

                                /**
                                 * @callback
                                 */
                                success: function (data) {
                                    if (!data.error && data.url) {
                                        dataPost().postData({
                                            action: data.url,
                                            data: {}
                                        });
                                    }
                                }
                            });
                        }
                    },
                    'buttons': [{
                        text: $.mage.__('Cancel'),
                        class: 'action-secondary action-dismiss',

                        /**
                         * @param {jQuery.Event} event
                         */
                        click: function (event) {
                            this.closeModal(event);
                        }
                    }, {
                        text: $.mage.__('Delete'),
                        class: 'action-primary action-accept',

                        /**
                         * @param {jQuery.Event} event
                         */
                        click: function (event) {
                            this.closeModal(event, true);
                        }
                    }]
                });

            }, this));
        }
    });

    return $.mage.postWrapper;
});
