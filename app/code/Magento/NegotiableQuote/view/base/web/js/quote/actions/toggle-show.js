/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mage.toggleShow', {
        options: {
            toggleBlockId: '',
            showBlockId: '',
            hideBlockId: '',
            typeEvent: 'click',
            hideSelf: false,
            delay: 0,
            toggleClass: {
                element: '',
                class: ''
            }
        },

        /**
         * This method binds elements found in this widget.
         * @private
         */
        _bind: function () {
            $(this.element).on(this.options.typeEvent, $.proxy(this._toggleShow, this));
            $(this.element).on('hide', $.proxy(this._hideElement, this));
            $(this.element).on('show', $.proxy(this._showElement, this));

            if (this.options.showBlockId) {
                $(this.element).on('click', $.proxy(this._showBlock, this));
            }

            if (this.options.hideBlockId) {
                $(this.element).on('click', $.proxy(this._hideBlock, this));
            }
        },

        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function () {
            this.options.toggleBlockId = this._checkId(this.options.toggleBlockId);

            if (this.options.toggleBlockId) {
                this._bind();
            }

            if (this.options.toggleClass.element) {
                this.options.toggleElement = this.element.find(this.options.toggleClass.element);
            }
        },

        /**
         * @private
         */
        _toggleClass: function () {
            this.options.toggleElement.toggleClass(this.options.toggleClass.class);
        },

        /**
         * This method show current block.
         * @private
         */
        _showBlock: function () {
            this.options.showBlockId = this._checkId(this.options.showBlockId);
            $(this.options.showBlockId).show();
        },

        /**
         * This method hide current block.
         * @private
         */
        _hideBlock: function () {
            this.options.hideBlockId = this._checkId(this.options.hideBlockId);
            $(this.options.hideBlockId).hide();
        },

        /**
         * This method show self.
         * @private
         */
        _showElement: function () {
            this.element.show();
        },

        /**
         * This method hide self.
         * @private
         */
        _hideElement: function () {
            this.element.hide();
        },

        /**
         * This method check id.
         * @private
         */
        _checkId: function (_id) {
            if (!_id) {
                return false;
            }

            if (typeof _id !== 'string') {
                return false;
            }

            if (_id[0] !== '#') {
                _id = '#' + _id;

                return _id;
            }

            return _id;
        },

        /**
         * This method toggle show block.
         * @private
         */
        _toggleShow: function (e) {
            if (this.options.toggleElement) {
                this._toggleClass();
            }
            e.preventDefault();
            $(this.options.toggleBlockId).slideToggle(this.options.delay);

            if (this.options.hideSelf) {
                this._hideElement();
            }
        }
    });

    return $.mage.toggleShow;
});
