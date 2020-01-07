/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'jquery-ui-modules/widget',
    'mage/translate'
], function ($, confirm) {
    'use strict';

    $.widget('mage.negotiableQuote', {

        /**
         * Options common to all instances of this widget.
         * @type {Object}
         */
        options: {
            deleteConfirmMessage: $.mage.__('Are you sure you want to delete this quote?')
        },

        /**
         * Create widget
         * @type {Object}
         */
        _create: function () {
            this.element.on('click', $.proxy(function () {
                this._deleteQuote();
            }, this));
        },

        /**
         * Delete the Negotiable Quote whose id is specified in a data attribute after confirmation from the user.
         * @private
         * @return {Boolean}
         */
        _deleteQuote: function () {
            var self = this;

            confirm({
                content: this.options.deleteConfirmMessage,
                actions: {

                    /**
                     * Confirms deletion
                     * @type {Object}
                     */
                    confirm: function () {
                        $(self.options.deleteLinkSelector).trigger('click');
                    }
                }
            });

            return false;
        }
    });

    return $.mage.negotiableQuote;
});
