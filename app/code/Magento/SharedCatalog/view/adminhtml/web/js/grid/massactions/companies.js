/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiLayout',
    'uiRegistry',
    'mage/translate',
    'Magento_SharedCatalog/js/grid/massactions'
], function (_, layout, registry, $t, Massactions) {
    'use strict';

    return Massactions.extend({
        defaults: {
            assignActionConfig: {
                name: '${ $.name }_action_assign'
            },
            unassignActionConfig: {
                name: '${ $.name }_action_unassign'
            },
            modules: {
                assignAction: '${ $.assignActionConfig.name }',
                unassignAction: '${ $.unassignActionConfig.name }'
            },
            actionComponents: {
                assign: 'assignAction',
                unassign: 'unassignAction'
            }
        },

        /**
         * Get actions components
         *
         * @returns {Array}
         */
        getActions: function () {
            return [this.assignActionConfig, this.unassignActionConfig];
        }
    });
});
