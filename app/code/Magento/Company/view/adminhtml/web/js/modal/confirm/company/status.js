/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/translate',
    'underscore',
    'jquery/validate',
    'mage/validation'
], function ($, confirm, $t, _) {
    'use strict';

    var defaultConfig = {
        title: $t('Change Company Status?'),
        modalClass: 'change-status',
        formSelector: '[data-role="change-status-confirm-form"]',
        buttons: [
            {
                text: $t('Cancel'),
                class: 'action-secondary action-dismiss',

                /**
                 * @param {jQuery.Event} e
                 */
                click: function (e) {
                    this.closeModal(e, false);
                }
            },
            {
                text: $t('Change status'),
                class: 'action-primary action-accept',

                /**
                 * @param {jQuery.Event} e
                 */
                click: function (e) {
                    if (this.options.isForm) {
                        if ($(this.options.formSelector).valid()) {
                            this.closeModal(e, true);
                        }
                    } else {
                        this.closeModal(e, true);
                    }
                }
            }
        ]
    };

    /**
     * Get form data
     *
     * @param {jQuery} form
     * @returns {Object}
     */
    function getFormData(form) {
        var data = $(form).serializeArray(),
            preparedData = {};

        _.each(data, function (field) {
            preparedData[field.name] = field.value;
        });

        return preparedData;
    }

    /**
     * Build confirm config
     *
     * @param {jQuery.Deferred} deferred
     * @param {Object} config
     * @returns {Object}
     */
    function buildConfig(deferred, config) {
        config = _.extend({}, defaultConfig, config);
        config.actions = {
            /**
             * Confirm action.
             */
            confirm: function () {
                var data;

                if (config.isForm) {
                    data = getFormData($(config.formSelector));
                }
                deferred.resolve(data);
            },

            /**
             * Cancel action.
             */
            cancel: function () {
                deferred.reject();
            }
        };

        return config;
    }

    return function (config) {
        var deferred = $.Deferred();

        config = buildConfig(deferred, config);
        confirm(config);

        $(config.formSelector).validation({
            errorClass: 'admin__field-error'
        });

        return deferred.promise();
    };
});
