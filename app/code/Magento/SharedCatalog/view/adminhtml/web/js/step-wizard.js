/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/lib/step-wizard',
    'Magento_SharedCatalog/js/wizard',
    'uiComponent',
    'Magento_Ui/js/lib/spinner',
    'jquery',
    'underscore',
    'ko',
    'mage/backend/notification'
], function (uiStepWizard, Wizard, Component, loader, $, _) {
    'use strict';

    return uiStepWizard.extend({

        /**
         * Init component
         */
        initialize: function () {
            this._super();

            _.defer(_.bind(this.hideLoader, this));
        },

        /**
         * Open step wizard
         */
        open: function () {
            this.selectedStep(_.first(this.stepsNames));
            this.wizard = new Wizard(this.steps);
            $('[data-role=step-wizard-dialog]').trigger('openModal');
        },

        /**
         * Close step wizard
         */
        close: function () {
            this.trigger('update-price', this._closeModal.bind(this));
        },

        /**
         * Close modal
         */
        _closeModal: function () {
            $('[data-role=step-wizard-dialog]').trigger('closeModal');
        },

        /**
         * Hides loader
         */
        hideLoader: function () {
            loader.get(this.name).hide();
        },

        /**
         * Shows loader
         */
        showLoader: function () {
            loader.get(this.name).show();
        },

        /**
         * Step back
         */
        back: function () {
            this.trigger('update-price', this._super.bind(this));
        }
    });
});
