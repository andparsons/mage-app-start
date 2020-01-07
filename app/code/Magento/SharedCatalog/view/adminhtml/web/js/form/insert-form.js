/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/form/components/insert-form'
], function ($, InsertForm) {
    'use strict';

    return InsertForm.extend({
        defaults: {
            errorContainerClass: 'message message-error error',
            listens: {
                responseData: 'afterUpdate'
            },
            events: {
                afterUpdate: []
            },
            modules: {
                toolbar: '${ $.toolbarContainer }',
                columns: '${ $.columnsProvider }'
            }
        },

        /**
         * Callback after response data update.
         */
        afterUpdate: function () {
            var form = this;

            this.events.afterUpdate.forEach(function (eventName) {
                form.trigger(eventName);
            });
            this.removeError();

            if (!this.responseData.data.status) {
                this.renderError(this.responseData.data.error);
            } else {
                this.toolbar().closeModal();
            }
        },

        /** @inheritdoc */
        destroyInserted: function () {
            this.removeError();

            return this._super();
        },

        /** @inheritdoc */
        render: function (params) {
            this.trigger('render-form', this._super.bind(this, params));
            this.columns('hideLoader');
        },

        /**
         * Insert error in toolbar.
         *
         * @param {String} error
         */
        renderError: function (error) {
            var $container = $('<div/>');

            $container
                .addClass(this.errorContainerClass)
                .append(error);
            this.formError = $container;
            $(this.toolbarSection).append(this.formError);
        },

        /**
         * Remove error toolbar.
         */
        removeError: function () {
            if (this.formError && this.formError.length) {
                this.formError.remove();
                this.formError = $();
            }
        }
    });
});
