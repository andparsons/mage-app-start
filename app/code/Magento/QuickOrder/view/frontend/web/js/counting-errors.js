/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/template',
    'text!Magento_QuickOrder/templates/error.html',
    'jquery-ui-modules/widget',
    'mage/translate'
], function ($, mageTemplate, errorTpl) {
    'use strict';

    $.widget('mage.countingErrors', {

        options: {
            nameErrorBlock: '',
            wrapError: '',
            event: 'addErrors',
            eventElement: '',
            showErrorElement: '',
            renderData: {
                countError: null,
                countErrorText: $.mage.__(' product(s) require(s) your attention.'),
                text: null
            }
        },

        /**
         * This method constructs a new widget.
         *
         * @private
         */
        _create: function () {
            if (typeof this.options.eventElement !== 'object') {
                this.options.eventElement = this.element;
            }

            if (this.options.showErrorElement === '') {
                this.options.showErrorElement = this.element;
            }
            this._bind();
            this.errorBlockTmpl = mageTemplate(errorTpl);
        },

        /**
         * This method binds elements found in this widget.
         *
         * @private
         */
        _bind: function () {
            $(this.options.eventElement).on(this.options.event, $.proxy(this._setError, this));
        },

        /**
         * This method set needed error.
         *
         * @param {Object} e
         * @param {Object} data
         *
         * @private
         */
        _setError: function (e, data) {
            var $addBtn = $('button.tocart'),
                renderData = this.options.renderData;

            if (data) {
                $.extend(renderData, data);
            }
            renderData.countError = $(this.options.wrapError).find(this.options.nameErrorBlock).length;
            this._dellError();

            if (renderData.countError || renderData.text) {
                this._renderError();
                $addBtn.prop('disabled', true);
            } else {
                $addBtn.prop('disabled', false);
            }
        },

        /**
         * This method delete error.
         *
         * @private
         */
        _dellError: function () {
            $(this.options.showErrorElement).html('');
        },

        /**
         *  This method render error.
         *
         * @private
         */
        _renderError: function () {
            var addedBlock,
                errorBlock = $(this.options.showErrorElement);

            // render the error
            addedBlock = $(this.errorBlockTmpl({
                data: this.options.renderData
            }));

            errorBlock.append(addedBlock);
            addedBlock.trigger('contentUpdated');
        }

    });

    return $.mage.countingErrors;
});
