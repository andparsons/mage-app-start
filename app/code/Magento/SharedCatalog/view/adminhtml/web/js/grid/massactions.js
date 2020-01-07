/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiLayout',
    'uiRegistry',
    'mage/translate',
    'Magento_Ui/js/grid/massactions'
], function (_, layout, registry, $t, Massactions) {
    'use strict';

    return Massactions.extend({
        defaults: {
            actionComponents: {}
        },

        /**
         * Initializes column assign component.
         *
         * @returns {Massactions} Chainable.
         */
        initialize: function () {
            this._super()
                .initActions();

            return this;
        },

        /**
         * Init actions
         *
         * @returns {Massactions}
         */
        initActions: function () {
            layout(this.getActions());

            return this;
        },

        /**
         * Get actions components
         *
         * @returns {Array}
         */
        getActions: function () {
            return [];
        },

        /**
         * Retrieves action object associated with a specified index.
         *
         * @param {String} actionIndex - Actions' identifier.
         * @returns {Object} Action object.
         */
        getAction: function (actionIndex) {
            var action = this._super(actionIndex);

            return this.getActionComponent(action);
        },

        /**
         * Get action component
         *
         * @param {Object} action
         * @returns {Action}
         */
        getActionComponent: function (action) {
            var componentProperty = this.actionComponents[action.type];

            if (componentProperty) {
                action = this[componentProperty].apply();
            }

            return action;
        }
    });
});
