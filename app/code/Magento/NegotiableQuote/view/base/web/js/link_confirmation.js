/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiElement',
    'underscore',
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/confirm'
], function (UiElement, _, $, $t, confirm) {
    'use strict';

    return UiElement.extend({
        defaults: {
            isEnable: true,
            modalConfig: {
                title: '',
                content: ''
            }
        },

        /**
         * Initializes
         *
         * @returns {Element} Chainable.
         */
        initialize: function () {
            this._super();
            _.bindAll(this, 'click');

            return this;
        },

        /**
         * Click observer
         *
         * @returns {Boolean}
         */
        click: function (viewModel, event) {
            var targetUrl;

            if (!this._isEnable()) {
                return true;
            }

            targetUrl = $(event.currentTarget).attr('href');

            confirm(_.extend({}, this.modalConfig, {
                buttons: [{
                    text: $t('Proceed'),
                    class: 'action primary confirm',

                    /**
                     * @param {jQuery.Event} e
                     */
                    click: function (e) {
                        window.location.href = targetUrl;
                        this.closeModal(e, true);
                    }
                }, {
                    text: $t('Cancel'),
                    class: 'action secondary cancel',

                    /**
                     * @param {jQuery.Event} e
                     */
                    click: function (e) {
                        this.closeModal(e);
                    }
                }]
            }));

            return false;
        },

        /**
         * Is confirm enable
         *
         * @returns {Boolean}
         */
        _isEnable: function () {
            return this.isEnable;
        }
    });
});
