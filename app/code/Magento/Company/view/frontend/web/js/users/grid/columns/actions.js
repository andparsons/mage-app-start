/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/grid/columns/actions',
    'Magento_Company/js/user-edit',
    'Magento_Company/js/user-delete',
    'Magento_Company/js/role-delete'
], function ($, Actions) {
    'use strict';

    return Actions.extend({
        defaults: {
            bodyTmpl: 'Magento_Company/users/grid/cells/actions'
        },

        /**
         * Callback after click on element.
         *
         * @public
         */
        applyAction: function () {
            switch (this.type) {
                case 'edit-user':
                    $(this).userEdit(this.options)
                        .trigger('editUser');
                    break;

                case 'delete-user':
                    $(this).userDelete(this.options)
                        .trigger('deleteUser');
                    break;

                case 'delete-role':
                    $(this).roleDelete(this.options)
                        .trigger('deleteRole');
                    break;

                default:
                    return true;
            }
        }
    });
});
