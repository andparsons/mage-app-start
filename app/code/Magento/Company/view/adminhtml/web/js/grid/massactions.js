/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/modal/confirm',
    'mage/translate',
    'Magento_Ui/js/grid/tree-massactions'
], function (confirm, $t, Massactions) {
    'use strict';

    return Massactions.extend({
        /**
         * Shows actions' confirmation window.
         *
         * @param {Object} action - Actions' data.
         * @param {Function} callback - Callback that will be
         *      invoked if action is confirmed.
         */
        _confirm: function (action, callback) {
            var confirmData = action.confirm;

            if (action.type !== 'delete') {
                return this._super(action, callback);
            }

            confirm({
                title: confirmData.title,
                content: confirmData.message,
                modalClass: 'confirm delete-company',
                actions: {
                    confirm: callback
                },
                buttons: [{
                    text: $t('Cancel'),
                    class: 'action-secondary action-dismiss',

                    /**
                     * @param {jQuery.Event} event
                     */
                    click: function (event) {
                        this.closeModal(event);
                    }
                }, {
                    text: $t('Delete'),
                    class: 'action-primary action-accept',

                    /**
                     * @param {jQuery.Event} event
                     */
                    click: function (event) {
                        this.closeModal(event, true);
                    }
                }]
            });
        }
    });
});
