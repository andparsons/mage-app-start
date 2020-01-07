/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/dataPost',
    'mage/translate',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert'
], function ($, dataPost, $t, confirm, alert) {
    'use strict';

    $.widget('mage.deleteCustomer', {
        options: {
            url: '',
            validate: '',
            text: $t('Are you sure you want to do this?'),
            content: $t('Sorry! You cannot delete this user: The user is the company admin.'),
            title: $t('Cannot Delete the Company Admin'),
            isActive: false
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
         * Bind events
         *
         * @private
         */
        _bind: function () {
            this._on(this.element, {
                'click': this.onDeleteCustomer
            });
        },

        /**
         * On delete customer handler.
         *
         * @private
         */
        onDeleteCustomer: function () {
            if (this.options.isActive) {
                return false;
            }

            this.options.isActive = true;

            $.when()
                .then(this.confirm.bind(this))
                .then(this.validate.bind(this))
                .then(this.deleteCustomer.bind(this))
                .always(function () {
                    this.options.isActive = false;
                }.bind(this));
        },

        /**
         * Confirm action.
         *
         * @return {Promise}
         * @private
         */
        confirm: function () {
            var deferred = $.Deferred();

            confirm({
                content: this.options.text,
                actions: {
                    /** @inheritdoc */
                    confirm: function () {
                        deferred.resolve();
                    },

                    /** @inheritdoc */
                    cancel: function () {
                        deferred.reject();
                    }
                }
            });

            return deferred.promise();
        },

        /**
         * Validate customer delete action.
         *
         * @returns {Promise}
         * @private
         */
        validate: function () {
            var deferred = $.Deferred();

            $.ajax({
                url: this.options.validate,
                type: 'get',
                dataType: 'json',
                showLoader: true
            }).done(function (data) {
                if (!data.deletable) {
                    this.showValidationAlert();
                }

                data.deletable ? deferred.resolve() : deferred.reject();
            }.bind(this)).fail(function () {
                deferred.reject();
            });

            return deferred.promise();
        },

        /**
         * Show error alert.
         *
         * @private
         */
        showValidationAlert: function () {
            alert({
                modalClass: 'restriction-modal-quote',
                responsive: true,
                innerScroll: true,
                title: this.options.title,
                content: this.options.content
            });
        },

        /**
         * Delete customer.
         *
         * @private
         */
        deleteCustomer: function () {
            dataPost().postData({
                action: this.options.url,
                data: {}
            });
        }
    });

    return $.mage.deleteCustomer;
});
