/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
        'jquery',
        'mage/tabs'
    ],
    function ($, tabs) {
        'use strict';

        $.widget('mage.negotiableQuoteTabs', tabs, {
            options: {
                openedState: ''
            },

            /**
             *
             * @private
             */
            _bind: function () {
                $(this.element).on('contentUpdated', this._reload.bind(this));
            },

            /**
             * Remove events
             *
             * @private
             */
            _unbind: function () {
                $.each(this.collapsibles, function () {
                    $(this).off();
                });
            },

            /**
             * Reload widget
             *
             * @private
             */
            _reload: function () {
                this._destroy();
                this._unbind();
                this._create();
            }
        });

        return $.mage.negotiableQuoteTabs;
    }
);
