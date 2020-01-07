/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiLayout',
    'mage/translate',
    'Magento_Ui/js/grid/columns/column'
], function (_, layout, $t, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            headerTmpl: 'Magento_SharedCatalog/grid/columns/assign',
            bodyTmpl: 'Magento_SharedCatalog/grid/cells/assign',
            fieldClass: {
                'admin__scope-old': true,
                'data-grid-onoff-cell': false,
                'data-grid-checkbox-cell': false
            },

            actions: [{
                value: 'selectAll',
                label: $t('Select All')
            }, {
                value: 'deselectAll',
                label: $t('Deselect All')
            }],

            assignClientConfig: {
                component: 'Magento_Ui/js/grid/editing/client',
                name: '${ $.name }_assign_client'
            },
            massAssignClientConfig: {
                component: 'Magento_Ui/js/grid/editing/client',
                name: '${ $.name }_mass_assign_client'
            },

            modules: {
                assignClient: '${ $.assignClientConfig.name }',
                massAssignClient: '${ $.massAssignClientConfig.name }',
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
         * Init clients
         *
         * @returns {AssignColumn}
         */
        initClients: function () {
            layout([this.assignClientConfig, this.massAssignClientConfig]);

            return this;
        },

        /**
         * Selects all records, even those that
         * are not visible on the page.
         *
         * @returns {AssignColumn} Chainable.
         */
        selectAll: function () {
            this._massAssignProducts(this._getAllItemsRequestData(), true);

            return this;
        },

        /**
         * Deselects all records.
         *
         * @returns {AssignColumn} Chainable.
         */
        deselectAll: function () {
            this._massAssignProducts(this._getAllItemsRequestData(), false);

            return this;
        },

        /**
         * Mass assign products
         *
         * @param {Object} requestData
         * @param {Boolean} isAssign
         * @private
         */
        _massAssignProducts: function (requestData, isAssign) {
            this.columns('showLoader');

            requestData['is_assign'] = +isAssign;

            this.massAssignClient()
                .save(requestData)
                .done(this._onAssignDone)
            ;
        },

        /**
         * Get request data for all items
         *
         * @returns {Object}
         * @private
         */
        _getAllItemsRequestData: function () {
            var data = {
                excluded: false,
                excludeMode: true
            };

            return _.extend(data, this.getFiltering());
        },

        /**
         * Extracts filtering data from data provider.
         *
         * @returns {Object} Current filters state.
         */
        getFiltering: function () {
            var source = this.source(),
                keys = ['filters', 'search', 'namespace'];

            if (!source) {
                return {};
            }

            return _.pick(source.get('params'), keys);
        },

        /**
         * Defines if provided select/deselect actions is relevant
         *
         * @returns {Boolean}
         */
        isActionRelevant: function () {
            return true;
        },

        /**
         * Toggle assign observer
         *
         * @param {Object} row
         */
        toggleAssign: function (row) {
            this._updateProductAssign(row['entity_id'], +!row['is_assign']);
        },

        /**
         * Update product assign
         *
         * @param {*} productId
         * @param {*} isAssign
         * @private
         */
        _updateProductAssign: function (productId, isAssign) {
            this.columns('showLoader');
            this.assignClient()
                .save(this._prepareRequestData(productId, isAssign))
                .done(this._onAssignDone)
            ;
        },

        /**
         * Prepare request data for assign
         *
         * @param {*} productId
         * @param {*} isAssign
         * @returns {Object}
         * @private
         */
        _prepareRequestData: function (productId, isAssign) {
            return {
                'product_id': productId,
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
            this.trigger('reassigned');
        }
    });
});
