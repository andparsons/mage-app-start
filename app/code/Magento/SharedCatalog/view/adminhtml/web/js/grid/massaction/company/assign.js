/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'uiLayout',
    'mage/translate',
    'uiComponent',
    'mage/template',
    'Magento_Ui/js/modal/confirm'
], function ($, _, layout, $t, Component, mageTemplate, confirm) {
    'use strict';

    return Component.extend({
        defaults: {
            confirmation: {
                contentTemplate: '<h1><%- header %></h1><div><%- message %></div>',
                text: {
                    header: '',
                    message: ''
                }
            },

            clientConfig: {
                component: 'Magento_Ui/js/grid/editing/client',
                name: '${ $.name }_client'
            },

            modules: {
                client: '${ $.clientConfig.name }',
                columns: '${ $.columnsProvider }',
                source: '${ $.provider }'
            }
        },

        /**
         * Initializes component.
         *
         * @returns {Assign} Chainable.
         */
        initialize: function () {
            _.bindAll(this, '_onSaveDone', '_onValidateDone');

            this._super()
                .initClients();

            return this;
        },

        /**
         * Init clients
         *
         * @returns {Assign}
         */
        initClients: function () {
            layout([this.clientConfig]);

            return this;
        },

        /**
         * Action callback
         *
         * @param {Object} action - Action data
         * @param {Object} data - Selections data
         */
        callback: function (action, data) {
            action._callback(data);
        },

        /**
         * Action callback
         *
         * @param {Object} data
         * @private
         */
        _callback: function (data) {
            return this['is_assign'] ? this._validateAssign(data) : this._processConfirmation(data);
        },

        /**
         * Process action confirmation
         *
         * @param {*} data
         * @private
         */
        _processConfirmation: function (data) {
            confirm({
                modalClass: 'confirm confirm-shared-catalog-change',
                content: this._getConfirmationContent(),
                actions: {
                    confirm: this.getConfirmCallback(data)
                },
                buttons: [{
                    text: $.mage.__('Cancel'),
                    'class': 'action-secondary action-dismiss',

                    /**
                     * @param {jQuery.Event} event
                     */
                    click: function (event) {
                        this.closeModal(event);
                    }
                }, {
                    text: $.mage.__('Proceed'),
                    'class': 'action-primary action-accept',

                    /**
                     * @param {jQuery.Event} event
                     */
                    click: function (event) {
                        this.closeModal(event, true);
                    }
                }]
            });
        },

        /**
         * Get confirmation popup content
         *
         * @returns {String}
         * @private
         */
        _getConfirmationContent: function () {
            return mageTemplate(this.confirmation.contentTemplate, this.confirmation.text);
        },

        /**
         * Get confirm callback
         *
         * @param {Object} data
         */
        getConfirmCallback: function (data) {
            return _.bind(this.assignCatalog, this, this['is_assign'], data);
        },

        /**
         * Apply catalog to selected companies
         *
         * @param {Number} value
         * @param {Object} selectedData
         */
        assignCatalog: function (value, selectedData) {
            var requestData;

            this.columns('showLoader');
            requestData = this._getRequestData(selectedData, value);
            this.client()
                .save(requestData)
                .done(this._onSaveDone);
        },

        /**
         * Validate assign
         *
         * @param {*}data
         * @private
         */
        _validateAssign: function (data) {
            var requestData;

            this.columns('showLoader');
            requestData = this._getRequestData(data);
            this.client()
                .validate(requestData)
                .done(_.bind(function (responseData) {
                    this._onValidateDone(data, responseData);
                }, this));
        },

        /**
         * Get request data
         *
         * @param {Object} data
         * @param {Boolean} isAssign
         * @returns {Object}
         * @private
         */
        _getRequestData: function (data, isAssign) {
            var itemsType = data.excludeMode ? 'excluded' : 'selected',
                selections = {};

            selections[itemsType] = data[itemsType];

            if (!selections[itemsType].length) {
                selections[itemsType] = false;
            }

            _.extend(selections, data.params || {});

            selections['is_assign'] = +isAssign;

            return selections;
        },

        /**
         * On validate done
         *
         * @param {*} data
         * @private
         */
        _onValidateDone: function (data, responseData) {
            this.columns('hideLoader');

            if (responseData['is_custom_assigned']) {
                this._processConfirmation(data);
            } else {
                this.getConfirmCallback(data).apply();
            }
        },

        /**
         * On save done callback
         *
         * @private
         */
        _onSaveDone: function () {
            this.columns('hideLoader');
            this.source('reload', {
                refresh: true
            });
        }
    });
});
