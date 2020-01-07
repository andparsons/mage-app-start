/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global AddBySku, Class, productConfigure, Ajax, jQuery */
/* eslint-disable strict, no-use-before-define */
define([
    'jquery',
    'prototype',
    'mage/translate',
    'Magento_NegotiableQuote/catalog/product/composite/configure'
], function (jquery) {

    window.AddBySku = Class.create();

    AddBySku.prototype = {
        /**
         * Constructor
         */
        initialize: function (data) {
            var originConfiguredCheck, originRequestConfiguration;

            this.removeFailedSkuUrl = data.removeFailedSkuUrl;
            this.removeAllFailedSkusUrl = data.removeAllFailedSkusUrl;
            this.addConfiguredUrl = data.addConfiguredUrl;
            this.fetchConfiguredUrl = data.fetchConfiguredUrl;
            this.quoteId = data.quoteId;
            this.configuredSkus = [];
            this.configurableItems = {};
            this.configItem = '';
            this.listType = 'errors';

            // Changing original productConfigure object for SKU items needs
            productConfigure.skuObject = this;
            productConfigure.useNegotiableQuote = true;

            originConfiguredCheck = productConfigure.itemConfigured;

            /** Check if item is configured */
            productConfigure.itemConfigured = function (listType, itemId) {
                var indexOfItemId;

                if (listType != this.skuObject.listType) { //eslint-disable-line eqeqeq
                    return originConfiguredCheck.apply(this, [listType, itemId]);
                }

                indexOfItemId = this.skuObject.configuredSkus.indexOf(itemId);

                if (indexOfItemId !== -1) {
                    if (!originConfiguredCheck.apply(this, [listType, itemId])) {
                        this.skuObject.configuredSkus.splice(indexOfItemId, 1);

                        return false;
                    }

                    return true;
                }

                return false;
            };
            originRequestConfiguration = productConfigure._requestItemConfiguration;

            /** Requests item configuration */
            productConfigure._requestItemConfiguration = function (listType, itemId) {
                if (listType == this.skuObject.listType) { //eslint-disable-line eqeqeq
                    itemId = this.skuObject.configurableItems[itemId];
                }

                return originRequestConfiguration.apply(this, [listType, itemId, this.skuObject.configItem]);
            };

            /** Abstract admin sales instance */
            function AdminSalesInstance(addBySkuObject) {
                var fields, i;

                this.skuInstance = addBySkuObject;

                /** Submit configured. */
                this.submitConfigured = function () {};

                /** Update error grid. */
                this.updateErrorGrid = function () {};

                /** On submit sku form. */
                this.onSubmitSkuForm = function () {};
                fields = $$(//eslint-disable-line no-undef
                    '#' + addBySkuObject.dataContainerId + ' input[name="sku"]',
                    '#' + addBySkuObject.dataContainerId + ' input[name="qty"]'
                );

                for (i = 0; i < fields.length; i++) {
                    Event.observe(fields[i], 'keypress', addBySkuObject.formKeyPress.bind(addBySkuObject));
                }
            }
            AdminCheckout.prototype = new AdminSalesInstance(this);
            AdminCheckout.prototype.constructor = AdminCheckout;

            /** Initialize parameters */
            function AdminCheckout() {
                this.controllerRequestParameterNames = {
                    customerId: 'customer',
                    storeId: 'store'
                };
            }

            /** Submit configured product */
            AdminCheckout.prototype.submitConfigured = function () {
                var area = ['errors', 'search', 'items', 'shipping_method', 'totals', 'giftmessage','billing_method'],
                    table = $('sku_errors_table'), //eslint-disable-line no-undef
                    elements = table.select('input[type=checkbox][name=sku_errors]:checked'),
                    fieldsPrepare = {};

                fieldsPrepare['from_error_grid'] = '1';
                elements.each(function (elem) {
                    var tr;

                    if (!elem.value || elem.value == 'on') { //eslint-disable-line eqeqeq
                        return;
                    }
                    tr = elem.up('tr');

                    if (tr) {
                        (function (fieldNames, parent, id) {
                            var i, el, paramKey;

                            if (typeof fieldNames == 'string') {
                                fieldNames = [fieldNames];
                            }

                            for (i = 0; i < fieldNames.length; i++) {
                                el = parent.select('input[name=' + fieldNames[i] + ']');
                                paramKey = 'add_by_sku[' + id + '][' + fieldNames[i] + ']';

                                if (el.length) {
                                    fieldsPrepare[paramKey] = el[0].value;
                                }
                            }
                        })(['qty', 'sku'], tr, elem.value);
                    }
                });
                this.skuInstance.productConfigureSubmit('errors', area, fieldsPrepare, this.skuInstance.configuredSkus);
                this.skuInstance.configuredSkus = [];
            };

            /** Update error grid */
            AdminCheckout.prototype.updateErrorGrid = function (params, url) {
                if (!params.json) {
                    params.json = true;
                }
                params['quote_id'] = this.skuInstance.quoteId;
                new Ajax.Request(url, {
                    parameters: params,
                    loaderArea: 'html-body',

                    /** Success callback */
                    onSuccess: function () {
                        jquery('button[data-role="update-quote"]').updateQuote('updateFailedSkus');
                    }
                });
            };

            this._provider = new AdminCheckout();
        },

        /**
         * Load area response handler
         *
         * @param {Object} response
         */
        loadAreaResponseHandler: function (response) {
            if (!response.errors) {
                // If response is empty loadAreaResponseHandler() won't update the area
                response.errors = '<span></span>';
            }
            // call origin response handler function
        },

        /**
         * Submit configured product
         *
         * @param {*} listType
         * @param {*} area
         * @param {Object} fieldsPrepare
         * @param {*} itemsFilter
         */
        productConfigureSubmit: function (listType, area, fieldsPrepare, itemsFilter) {
            var url = this.addConfiguredUrl,
                fields, name;

            // prepare additional fields
            fieldsPrepare['reset_shipping'] = 1;
            fieldsPrepare.json = 1;
            fieldsPrepare['quote_id'] = this.quoteId;

            // create fields
            fields = [];

            for (name in fieldsPrepare) { //eslint-disable-line guard-for-in
                fields.push(new Element('input', {
                    type: 'hidden',
                    name: name,
                    value: fieldsPrepare[name]
                }));
            }
            productConfigure.addFields(fields);

            // filter items
            if (itemsFilter) {
                productConfigure.addItemsFilter(listType, itemsFilter);
            }

            // prepare and do submit
            productConfigure.addListType(listType, {
                urlSubmit: url
            });
            productConfigure.setOnLoadIFrameCallback(listType, function (response) {
                this.loadAreaResponseHandler(response);
            }.bind(this));
            productConfigure.submit(listType);
            // clean
            this.productConfigureAddFields = {};
        },

        /**
         * Remove failed item
         *
         * @param {Object} obj
         */
        removeFailedItem: function (obj) {
            var sku = obj.up('tr').select('td')[0].select('input[name="sku"]')[0].value;

            this._provider.updateErrorGrid(
                {
                    'remove_sku': sku
                },
                this.removeFailedSkuUrl
            );
        },

        /**
         * Remove all failed items
         */
        removeAllFailed: function () {
            this._provider.updateErrorGrid(
                {
                    'sku_remove_failed': '1'
                },
                this.removeAllFailedSkusUrl
            );
        },

        /**
         * Submit configured item
         */
        submitConfigured: function () {
            this._provider.submitConfigured();
        },

        /**
         * Configure a product
         *
         * @param {*} id - Product ID
         */
        configure: function (id, sku, productId) {
            var productRow = $('sku_errors_table'), //eslint-disable-line no-undef
                noticeElement = [],
                productQtyElement, url;

            if (productRow && productRow.select('div[id=sku_' + sku + ']')[0]) {
                productRow = productRow.select('div[id=sku_' + sku + ']')[0];
                noticeElement = productRow.select('.notice');
                productQtyElement = productRow.up('tr').select('input[name=qty]')[0];
                this.listType = 'errors';
                this.configItem = '';
            } else {
                productRow = undefined;
            }
            productConfigure.setFieldsData(productRow, productId);

            if (productId) {
                productRow = $$('[data-product-id="' + productId + '"]')[0]; //eslint-disable-line no-undef
                productQtyElement = productRow.select('input[name="item[' + productId + '][qty]"]')[0];
                this.listType = 'grid';
                this.configItem = productRow.select('input[name="item[' + productId + '][config]"]')[0].value;
            }

            if (typeof this.configurableItems[sku] === 'undefined') {
                this.configurableItems[sku] = id;
            }

            // Don't process configured element by addBySku() observer method (it won't be serialized by serialize())
            productConfigure.setConfirmCallback(this.listType, function () {
                var $qty;

                // It is vital to push string element, check this line in configure.js:
                // this.itemsFilter[listType].indexOf(itemId) != -1
                productConfigure.skuObject.configuredSkus.push(sku.toString());

                if (noticeElement.length) {
                    // Remove message saying product requires configuration
                    noticeElement[0].remove();
                }

                if (productRow) {
                    $qty = productConfigure.getCurrentConfirmedQtyElement();

                    if ($qty) { // Product set does not have this
                        // Synchronize qtys between configure window and grid
                        productQtyElement.value = $qty.value;
                        jQuery(productQtyElement).trigger('change');
                    }
                }

            });

            url = this.fetchConfiguredUrl;
            productConfigure.addListType(this.listType, {
                urlFetch: url
            });

            productConfigure.showItemConfiguration(this.listType, sku);
            productConfigure.setShowWindowCallback(this.listType, function () {
                var qty, formCurrentQty;

                // sync qty of grid and qty of popup
                if (productRow) {
                    qty = productQtyElement.value;
                }

                if (qty && !isNaN(qty) && productRow) {
                    formCurrentQty = productConfigure.getCurrentFormQtyElement();

                    if (formCurrentQty) {
                        formCurrentQty.value = qty;
                    }
                }
            });
        }
    };
});
