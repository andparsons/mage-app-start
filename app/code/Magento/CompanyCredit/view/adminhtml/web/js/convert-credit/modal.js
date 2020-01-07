/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mageUtils',
    'Magento_Ui/js/modal/modal-component',
    'Magento_CompanyCredit/js/grid/massaction/selections-converter'
], function (_, utils, Modal, selectionsConverter) {
    'use strict';

    return Modal.extend({
        defaults: {
            actionSelections: null,
            modules: {
                currencyCode: '${ $.currencyCode }',
                ratesFields: '${ $.ratesFields }'
            }
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Component} Chainable.
         */
        initObservable: function () {
            this._super().observe('actionSelections');

            return this;
        },

        /**
         * Open modal window.
         *
         * @param {Component} component - Magento UI Component.
         * @param {Object} data - Company listing table options.
         */
        openModal: function (component, data) {
            var selections = selectionsConverter.convert(data);

            this.actionSelections(selections);
            this._super();
        },

        /**
         * Close modal window.
         */
        closeModal: function () {
            this._resetFields();
            this._super();
        },

        /**
         * Mass update converting of credit.
         */
        convertCredit: function () {
            var data;

            this.valid = true;
            this.validate(this.currencyCode());
            this.validate(this.ratesFields());

            if (this.valid) {
                data = {
                    'currency_to': this.currencyCode().value(),
                    'currency_rates': this.ratesFields().getUpdatedRates()
                };
                data = _.extend(data, this.actionSelections());

                utils.submit({
                    url: this.massConvertUrl,
                    data: data
                });

                this.closeModal();
            }
        },

        /**
         * Reset and clear fields of modal.
         *
         * @private
         */
        _resetFields: function () {
            this.currencyCode().reset();
            this.ratesFields().rates([]);
        }
    });
});
