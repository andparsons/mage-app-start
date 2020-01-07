/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/select',
    'Magento_Company/js/modal/confirm/company/status',
    'text!Magento_Company/template/modal/company/status/default.html',
    'text!Magento_Company/template/modal/company/status/reject.html',
    'mage/template'
], function ($, _, Select, confirm, modalDefaultTemplate, modalRejectTemplate, mageTemplate) {
    'use strict';

    return Select.extend({
        confirm: {
            templates: {
                base: modalDefaultTemplate,
                reject: modalRejectTemplate
            },
            handlers: {}
        },

        /**
         * Init ui component
         *
         * @returns {Element}
         */
        initialize: function () {
            return this._super()
                .initStateConfig();
        },

        /**
         * Init state config
         *
         * @returns {Element}
         */
        initStateConfig: function () {
            this.initialValue = this.value();
            this.confirm.handlers = {
                reject: this._rejectConfirmHandler
            };

            return this;
        },

        /**
         * Callback that fires when 'value' property is updated.
         */
        onUpdate: function () {
            this._super();

            if (this.initialValue !== this.value()) {
                this._confirmChanges();
            }
        },

        /**
         * Show confirmation popup
         *
         * @private
         */
        _confirmChanges: function () {
            var statusConfig = this.confirmation.status[this.value()];

            confirm(this._getConfirmationConfig(this.value()))
                .done(function (data) {
                    var handler;

                    this.initialValue = this.value();
                    handler = this.confirm.handlers[statusConfig.handler];

                    if (_.isFunction(handler)) {
                        handler.call(this, data);
                    }
                }.bind(this))
                .fail(function () {
                    this.reset();
                }.bind(this));
        },

        /**
         * Get confirmation config
         *
         * @param {String} status
         * @returns {Object}
         * @private
         */
        _getConfirmationConfig: function (status) {
            var statusConfig = this.confirmation.status[status],
                contentTemplate = this.confirm.templates[statusConfig.template],
                content = mageTemplate(contentTemplate, this.confirmation.status[status]),
                confirmationConfig = _.extend(
                    {
                        content: content
                    },
                    this.confirmation.config,
                    this.confirmation.status[status]
                );

            return confirmationConfig;
        },

        /**
         * Reject confirm handler
         *
         * @param {Object} data
         * @returns {Select}
         * @private
         */
        _rejectConfirmHandler: function (data) {
            this.source.set('data.general.reject_reason', data.reason);

            return this;
        }
    });
});
