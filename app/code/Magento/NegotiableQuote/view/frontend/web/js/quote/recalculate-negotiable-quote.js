/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
        'jquery'
    ],
    function ($) {
        'use strict';

        $.widget('mage.recalculateNegotiableQuote', {
            options: {
                url: '',
                itemsBlockId: '#items-quoted',
                tabsContainer: '.quote-details-items',
                quoteId: '',
                canUpdate: '',
                messagesBlockId: '#messages',
                addressBlockId: '#quote-address',
                sendBtn: '.action.send'
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
             *
             * @private
             */
            _bind: function () {
                this.ajaxPOST();
            },

            /**
             * Ajax request to recalculate quote and return updated data.
             *
             * @private
             */
            ajaxPOST: function () {
                if (this.options.canUpdate) {
                    $.ajax({
                        url: this.options.url,
                        data: {
                            'quote_id': this.options.quoteId
                        },
                        type: 'post',
                        showLoader: true,

                        /**
                         * @callback
                         */
                        success: $.proxy(function (data) {
                            $(this.options.itemsBlockId).replaceWith(data['quote_items']);
                            $(this.options.messagesBlockId).replaceWith(data['quote_messages']);
                            $(this.options.addressBlockId).html(data.address);
                            $(this.options.tabsContainer).trigger('contentUpdated');
                            $(this.options.sendBtn).trigger('reloadWidget');
                        }, this)
                    });
                }
            }
        });

        return $.mage.recalculateNegotiableQuote;
    }
);
