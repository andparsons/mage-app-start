/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'underscore',
    'mage/translate',
    'Magento_Ui/js/modal/confirm',
    'mage/template'
], function (Component, $, _, $t, confirm, mageTemplate) {
    'use strict';

    return Component.extend({
        defaults: {
            eventType: 'click',
            popupTitle: $t('Proceed to Store Configuration?'),
            popupMessage: $t('You\'ve made changes into the shared catalog. If you leave this page now, the changes will be lost.'), //eslint-disable-line max-len
            contentTemplate: '<p><%- message %></p>',
            notificationSelector: '[data-role=notification-dialog]',
            containerSelector: '.shared-catalog-sharedcatalog-wizard',
            fieldName: 'isChanged',
            isNotificationEnabled: false,
            listens: {
                '${ $.providerStructure }:data': '_onStructureDataChanged',
                '${ $.providerPricing }:data': '_onPricingDataChanged'
            }
        },

        /**
         * Initializes notification component, sets event listeners on appropriate selectors.
         */
        initialize: function () {
            this._super()
                .setModal();
            $(this.containerSelector).on(this.eventType, this.notificationSelector, this.showModal.bind(this));
        },

        /**
         * Sets options for modal window.
         */
        setModal: function () {
            var templateData = {
                    message: this.popupMessage
                };

            this.popupOptions = {
                modalClass: 'notification-popup',
                responsive: true,
                title: $t(this.popupTitle),
                content: this._getConfirmationContent(templateData)
            };
        },

        /**
         * Initializes buttons for modal window.
         * Shows modal window.
         *
         * @param {jQuery.Event} event
         */
        showModal: function (event) {
            if (this.isNotificationEnabled) {
                event.stopImmediatePropagation();
                event.preventDefault();
                this.popupOptions.buttons = [{
                    text: $t('Cancel'),
                    'class': 'action-secondary cancel',

                    /**
                     * @param {jQuery.Event} e
                     */
                    click: function (e) {
                        this.closeModal(e.currentTarget);
                    }
                }, {
                    text: $t('Proceed'),
                    'class': 'action-primary confirm',

                    /**
                     * @param {jQuery.Event} e
                     */
                    click: function (e) {
                        window.location.href = event.target.href;
                        this.closeModal(e, true);
                    }
                }];
                confirm(this.popupOptions);
            }
        },

        /**
         * Gets template for confirmation popup content.
         *
         * @param {Object} data
         * @returns {String}
         * @private
         */
        _getConfirmationContent: function (data) {
            return mageTemplate(this.contentTemplate, data);
        },

        /**
         * Updates value of 'sourceStructure' with initial data of an appropriate provider and
         * compares it with the updated data of the provider after any changes into the shared catalog structure
         * have been made. Changes value of `isNotificationEnabled` property.
         *
         * @param {Object} source
         * @private
         */
        _onStructureDataChanged: function (source) {
            if (_.isUndefined(this.sourceStructure)) {
                this.sourceStructure = source;
            }

            if (!this.isNotificationEnabled && !_.isEqual(this.sourceStructure, source)) {
                this.isNotificationEnabled = true;
            }
        },

        /**
         * Updates value of 'sourcePricing' with initial data of an appropriate provider and
         * compares it with the updated data of the provider after any changes into the shared catalog pricing
         * have been made. Changes value of `isNotificationEnabled` property.
         *
         * @param {Object} source
         * @private
         */
        _onPricingDataChanged: function (source) {
            if (_.isUndefined(this.sourcePricing)) {
                this.sourcePricing = source;
            }

            if (!this.isNotificationEnabled && (!_.isEqual(this.sourcePricing, source) || source[this.fieldName])) {
                this.isNotificationEnabled = true;
            }
        }
    });
});
