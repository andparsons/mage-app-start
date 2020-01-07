/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/modal/modal-component'
], function (Modal) {
    'use strict';

    return Modal.extend({
        defaults: {
            modules: {
                emailProvider: '${ $.emailProvider }'
            }
        },

        /**
         * Open modal.
         */
        openModal: function () {
            this.setTitle(this.options.title);
            this._super();
        },

        /**
         * Cancels changing of company admin.
         */
        adminChangeCancel: function () {
            this.emailProvider().reset();
            this.closeModal();
        },

        /**
         * Keeps old company admin active.
         */
        adminChangeActive: function () {
            this.emailProvider().updateSource(true);
            this.closeModal();
        }
    });
});
