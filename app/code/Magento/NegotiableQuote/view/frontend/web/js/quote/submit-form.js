/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['jquery'], function ($) {
    'use strict';

    $.widget('mage.submitForm', {
        options: {
            form: '[data-action="comment-form"]',
            event: 'click',
            watchField: '',
            enableBtn: ''
        },

        /**
         * Create widget.
         *
         * @type {Object}
         */
        _create: function () {
            var self = this;

            if (this.options.watchField && this.options.enableBtn) {
                this.fields = $(this.options.watchField);
                this.btn = $(this.options.enableBtn);
                this.oldVal = {};
                this.fields.each(function (key, value) {
                    self.oldVal[value.id] = value.value;
                });
            }
            this._bind();

        },

        /**
         * Add events on item.
         *
         * @private
         */
        _bind: function () {
            if (this.fields && this.btn) {
                this.fields.on('change', $.proxy(this._watch, this));
            }
            $(this.element).on(this.options.event, $.proxy(this._submitForm, this));
            $(this.element).on('reloadWidget', this._reloadWidget.bind(this));

        },

        /**
         * Remove events
         *
         * @private
         */
        _unbind: function () {
            $(this.element).off(this.options.event);
            $(this.element).off('reloadWidget');
        },

        /**
         * Reload widget
         *
         * @private
         */
        _reloadWidget: function () {
            this.destroy();
            this._unbind();
            this._create(this.options);
        },

        /**
         * Watch to change file.
         *
         * @private
         */
        _watch: function () {
            var self = this,
                isChange = false;

            this.fields.each(function (key, value) {
                if (value.value != self.oldVal[value.id]) { //eslint-disable-line eqeqeq
                    isChange = true;
                }
            });
            isChange ? this.btn.removeClass('_disabled').prop('disabled', false)
                : this.btn.addClass('_disabled').prop('disabled', true);
        },

        /**
         * Submit target form.
         *
         * @private
         */
        _submitForm: function () {
            $(this.options.form).submit();
        }

    });

    return $.mage.submitForm;
});
