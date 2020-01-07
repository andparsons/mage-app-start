/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    /**
     * @param {*} steps
     * @constructor
     */
    var Wizard = function (steps) {
        this.steps = steps;
        this.index = 0;
        this.data = {};
        this.element = $('[data-role=steps-wizard-main]');
        this.nextLabel = '[data-role="step-wizard-next"]';
        this.prevLabel = '[data-role="step-wizard-prev"]';
        this.nextLabelText = 'Next';
        this.prevLabelText = 'Back';
        $(this.element).notification();

        /**
         * @param {*} newIndex
         */
        this.move = function (newIndex) {
            if (!this.preventSwitch(newIndex)) {
                if (newIndex > this.index) {
                    this._next(newIndex);
                } else if (newIndex < this.index) {
                    this._prev(newIndex);
                }
            }
            this.updateButtons(this.getStep());
            this.showNotificationMessage();

            return this.getStep().name;
        };

        /**
         * @return {*}
         */
        this.next = function () {
            this.move(this.index + 1);

            return this.getStep().name;
        };

        /**
         * @return {*}
         */
        this.prev = function () {
            this.move(this.index - 1);

            return this.getStep().name;
        };

        /**
         * @param {*} newIndex
         * @return {Boolean}
         */
        this.preventSwitch = function (newIndex) {
            return newIndex < 0 || newIndex - this.index > 1;
        };

        /**
         * @return {*}
         */
        this._next = function (newIndex) {
            newIndex = _.isNumber(newIndex) ? newIndex : this.index + 1;

            try {
                _.isFunction(this.getStep().force) ? this.getStep().force(this) : false;

                if (newIndex >= steps.length) {
                    return false;
                }
            } catch (e) {
                this.setNotificationMessage(e.message, true);

                return false;
            }
            this.cleanErrorNotificationMessage();
            this.index = newIndex;
            this.cleanNotificationMessage();
            this.render();
        };

        /**
         * @param {*} newIndex
         * @private
         */
        this._prev = function (newIndex) {
            newIndex = _.isNumber(newIndex) ? newIndex : this.index - 1;
            this.index = newIndex;
        };

        /**
         * @param {*} stepIndex
         * @return {*|Object}
         */
        this.getStep = function (stepIndex) {
            return this.steps[stepIndex || this.index] || {};
        };

        /**
         * @param {*} message
         * @param {*} error
         */
        this.notifyMessage = function (message, error) {
            $(this.element).notification('clear').notification('add', {
                error: error,
                message: message
            });
        };

        /**
         * @param {Object} step
         */
        this.updateButtons = function (step) {
            if (steps[1].index === step.index) {
                this.element.find(this.nextLabel).addClass('button-last-step');
            } else {
                this.element.find(this.nextLabel).removeClass('button-last-step');
            }
            this.updateLabels(step);
        };

        /**
         * @param {Object} step
         */
        this.updateLabels = function (step) {
            this.element.find(this.nextLabel).find('button').text(step.nextLabelText || this.nextLabelText);
            this.element.find(this.prevLabel).find('button').text(step.prevLabelText || this.prevLabelText);
        };

        /** Show notification message. */
        this.showNotificationMessage = function () {
            if (!_.isEmpty(this.getStep())) {
                this.hideNotificationMessage();

                if (this.getStep().notificationMessage.text !== null) {
                    this.notifyMessage(
                        this.getStep().notificationMessage.text,
                        this.getStep().notificationMessage.error
                    );
                }
            }
        };

        /** Clean notification message. */
        this.cleanNotificationMessage = function () {
            this.getStep().notificationMessage.text = null;
            this.hideNotificationMessage();
        };

        /** Clean error notification message */
        this.cleanErrorNotificationMessage = function () {
            if (this.getStep().notificationMessage.error === true) {
                this.cleanNotificationMessage();
            }
        };

        /**
         * @param {*} text
         * @param {*} error
         */
        this.setNotificationMessage = function (text, error) {
            error = error !== undefined;

            if (!_.isEmpty(this.getStep())) {
                this.getStep().notificationMessage.text = text;
                this.getStep().notificationMessage.error = error;
                this.showNotificationMessage();
            }
        };

        /** Hide notification message. */
        this.hideNotificationMessage = function () {
            $(this.element).notification('clear');
        };

        /** Render. */
        this.render = function () {
            this.hideNotificationMessage();
            this.getStep().render(this);
        };

        /** Init. */
        this.init = function () {
            this.updateButtons(this.getStep());
            this.render();
        };
        this.init();
    };

    return Wizard;
});
