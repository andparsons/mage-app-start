/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'uiLayout',
    'mage/translate',
    'Magento_Ui/js/modal/prompt',
    'text!Magento_SharedCatalog/template/modal/modal-prompt-content.html',
    'Magento_Ui/js/grid/massactions',
    'Magento_SharedCatalog/js/utils/validator/event_key',
    'Magento_Ui/js/lib/key-codes'
], function ($, _, layout, $t, prompt, promptContentTmpl, Massactions, EventValidator, keyCodes) {
    'use strict';

    return Massactions.extend({
        defaults: {
            modules: {
                client: '${ $.clientConfig.name }',
                columns: '${ $.columnsProvider }'
            },
            clientConfig: {
                component: 'Magento_Ui/js/grid/editing/client',
                name: '${ $.name }_update_prices_client'
            },
            promptValue: '',
            requestUrl: ''
        },

        /**
         * Calls 'initObservable' of parent, initializes 'options' and 'initialOptions'
         *     properties, calls 'setOptions' passing options to it
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super().observe([
                'promptValue',
                'requestUrl'
            ]);

            return this;
        },

        /**
         * Initializes column custom price component.
         *
         * @returns {Pricing} Chainable.
         */
        initialize: function () {
            _.bindAll(this, '_onSaveDone');

            this._super()
                .initClients();

            return this;
        },

        /**
         * Init clients
         *
         * @returns {Pricing}
         */
        initClients: function () {
            layout([this.clientConfig]);

            return this;
        },

        /**
         * Shows actions' confirmation window.
         *
         * @param {Object} action - Actions' data.
         */
        _confirm: function (action) {
            var confirmData = action.confirm,
                self = this;

            this.requestUrl(action.url);
            this.promptDialog = prompt({
                promptContentTmpl: promptContentTmpl,
                title: confirmData.title,
                modalClass: 'prompt ' + confirmData.promptClass,
                label: confirmData.label,
                content: confirmData.message,
                addonPostfix: '%',
                actions: {
                    /**
                     * @param {*} value
                     */
                    confirm: function (value) {
                        self.columns('showLoader');
                        self.promptValue(value);
                        self.trigger('update-price', self._sendRequest.bind(self));
                    },

                    /**
                     * @return {Boolean}
                     */
                    cancel: function () {
                        return false;
                    }
                },
                buttons: [{
                    text: $t('Cancel'),
                    'class': 'action-secondary action-dismiss',

                    /** Click action. */
                    click: function () {
                        this.closeModal();
                    }
                }, {
                    text: $t('Apply'),
                    class: 'action-primary action-accept',

                    /** Click action. */
                    click: function () {
                        this.closeModal(true);
                    }
                }]
            });
            this._setValueValidation(confirmData.promptClass);
        },

        /**
         * Allows to input only 1-100
         *
         * @param {String} promptClass
         * @private
         */
        _setValueValidation: function (promptClass) {
            var $prompt = $('.' + promptClass),
                $input = $prompt.find('input'),
                self = this;

            $prompt.keydown(function (e) {
                var keyName = keyCodes[e.keyCode];

                if (!EventValidator.isDigits(e)) {
                    e.preventDefault();
                }

                if (self.isEnterKey(keyName)) {
                    e.preventDefault();
                    self.promptDialog.prompt('closeModal', true);
                }
            });

            $prompt.keyup(function () {
                if (parseInt($input.val()) > 100) { //eslint-disable-line radix
                    $input.val(100);
                }
            });
        },

        /**
         * Checked key name
         *
         * @param {String} keyName
         * @returns {Boolean} if enter key return true
         */
        isEnterKey: function (keyName) {
            return keyName === 'enterKey';
        },

        /**
         * Get request data
         *
         * @param {Number} value
         * @param {Object} data
         * @returns {Object}
         * @private
         */
        _getRequestData: function (value, data) {
            var itemsType = data.excludeMode ? 'excluded' : 'selected',
                websiteId = this.source.get('data.websites.selected'),
                selections = {};

            selections[itemsType] = data[itemsType];
            selections['website_id'] = websiteId;

            if (!selections[itemsType].length) {
                selections[itemsType] = false;
            }

            _.extend(selections, data.params || {});

            selections.value = value;

            return selections;
        },

        /**
         * Send request data
         *
         * @private
         */
        _sendRequest: function () {
            var selectedData = this.getSelections();

            this.client().saveUrl = this.requestUrl();
            this.client()
                .save(this._getRequestData(this.promptValue(), selectedData))
                .done(this._onSaveDone);
        },

        /**
         * On save done callback
         *
         * @private
         */
        _onSaveDone: function () {
            this.columns('hideLoader');
            this.trigger('price-updated');
        }
    });
});
