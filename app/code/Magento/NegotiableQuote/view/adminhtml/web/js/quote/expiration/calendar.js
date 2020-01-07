/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'calendar'
], function ($, calendar) {
    'use strict';

    $.widget('mage.negotiableQuoteExpirationCalendar', calendar.calendar, {
        dateTimeFormat: {
            date: {
                'yy': 'y'
            }
        }
    });

    return $.mage.negotiableQuoteExpirationCalendar;
});
