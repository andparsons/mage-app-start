/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiLayout',
    'mage/translate',
    'Magento_Ui/js/grid/columns/column',
    'mage/template',
    'Magento_Ui/js/modal/confirm'
], function (_, layout, $t, Column, mageTemplate, confirm) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Magento_SharedCatalog/grid/cells/company/assign',

            confirmation: {
                contentTemplate: '<h1><%- header %></h1><div><%- message %></div>',
                text: {
                    header: '',
                    'message_assign': '',
                    'message_unassign': ''
                }
            },

            'link_text': {
                assign: $t('Assign'),
                unassign: $t('Unassign')
            },

            assignClientConfig: {
                component: 'Magento_Ui/js/grid/editing/client',
                name: '${ $.name }_assign_client'
            },

            modules: {
                assignClient: '${ $.assignClientConfig.name }',
                columns: '${ $.columnsProvider }'
            }
        },

        /**
         * Initializes column assign component.
         *
         * @returns {AssignColumn} Chainable.
         */
        initialize: function () {
            _.bindAll(this, '_onAssignDone');

            this._super()
                .initClients();

            return this;
        },

        /**
         * Is action available
         *
         * @param {Object} row
         * @returns {Boolean}
         */
        isAvailable: function (row) {
            return !(row['is_public_catalog'] && row['is_current']);
        },

        /**
         * Get link label
         *
         * @param {Object} row
         * @returns {String}
         */
        getLabel: function (row) {
            return row['is_current'] ? this['link_text'].unassign : this['link_text'].assign;
        },

        /**
         * Init clients
         *
         * @returns {AssignColumn}
         */
        initClients: function () {
            layout([this.assignClientConfig]);

            return this;
        },

        /**
         * Toggle assign observer
         *
         * @param {Object} row
         */
        toggleAssign: function (row) {
            var isAssign = !row['is_current'],
                assignCallback = _.bind(function () {
                    this._updateCompanyAssign(row['entity_id'], +isAssign);
                }, this);

            if (isAssign) {
                if (row['is_public_catalog']) {
                    assignCallback.apply();
                } else {
                    this._confirmAssign(assignCallback);
                }
            } else {
                this._confirmUnassign(assignCallback);
            }
        },

        /**
         * Confirm unassign
         *
         * @param {Function} callback
         * @private
         */
        _confirmUnassign: function (callback) {
            var templateData = this.confirmation.text;

            templateData.message = templateData['message_unassign'];
            this._confirm(templateData, callback);
        },

        /**
         * Confirm assign
         *
         * @param {Function} callback
         * @private
         */
        _confirmAssign: function (callback) {
            var templateData = this.confirmation.text;

            templateData.message = templateData['message_assign'];
            this._confirm(templateData, callback);
        },

        /**
         * Confirm action popup
         *
         * @param {*} templateData
         * @param {Function} callback
         * @private
         */
        _confirm: function (templateData, callback) {
            confirm({
                modalClass: 'confirm confirm-shared-catalog-change',
                content: this._getConfirmationContent(templateData),
                actions: {
                    confirm: callback
                },
                buttons: [{
                    text: $t('Cancel'),
                    'class': 'action-secondary action-dismiss',

                    /**
                     * @param {jQuery.Event} event
                     */
                    click: function (event) {
                        this.closeModal(event);
                    }
                }, {
                    text: $t('Proceed'),
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
        _getConfirmationContent: function (data) {
            return mageTemplate(this.confirmation.contentTemplate, data);
        },

        /**
         * Update company assign
         *
         * @param {*} companyId
         * @param {*} isAssign
         * @private
         */
        _updateCompanyAssign: function (companyId, isAssign) {
            this.columns('showLoader');
            this.assignClient()
                .save(this._prepareRequestData(companyId, isAssign))
                .done(this._onAssignDone)
            ;
        },

        /**
         * Prepare request data for assign
         *
         * @param {*}companyId
         * @param {*} isAssign
         * @returns {Object}
         * @private
         */
        _prepareRequestData: function (companyId, isAssign) {
            return {
                'company_id': companyId,
                'is_assign': isAssign
            };
        },

        /**
         * On assign done callback
         *
         * @private
         */
        _onAssignDone: function () {
            this.columns('hideLoader');
            this.source('reload', {
                refresh: true
            });
        }
    });
});
