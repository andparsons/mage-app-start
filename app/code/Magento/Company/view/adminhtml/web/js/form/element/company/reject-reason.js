/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'Magento_Ui/js/form/element/abstract',
    'moment',
    'mageUtils'
], function (ko, Element, moment, utils) {
    'use strict';

    return Element.extend({
        defaults: {
            elementTmpl: 'Magento_Company/form/element/company/reject-reason',
            dateFormat: 'MMM d, YYYY',
            rejectedAt: ko.observable(),
            listens: {
                value: '_updateVisibility'
            }
        },

        /**
         * Init ui component
         *
         * @returns {Element}
         */
        initialize: function () {
            return this._super()
                .initStateConfig();
        },

        /**
         * Init state config
         *
         * @returns {Element}
         */
        initStateConfig: function () {
            this.dateFormat = utils.normalizeDate(this.dateFormat);
            this.rejectedAt(this.source.get('data.general.rejected_at'));
            this.setVisible(!!this.value());

            return this;
        },

        /**
         * Get rejectedAt label
         *
         * @returns {String}
         */
        getRejectedAtLabel: function () {
            var date = moment(this.rejectedAt());

            return date.isValid() ? date.format(this.dateFormat) : '';
        },

        /**
         * Update visibility
         *
         * @private
         */
        _updateVisibility: function () {
            this.setVisible(!!this.value());
        }
    });
});
