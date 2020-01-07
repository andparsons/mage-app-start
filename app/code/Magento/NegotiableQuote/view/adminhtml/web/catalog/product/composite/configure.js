/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'prototype',
    'Magento_Catalog/catalog/product/composite/configure'
], function (jQuery) {
    'use strict';

    /* eslint-disable no-undef */
    var productConfigure = {

        /**
         * Submit configured data through iFrame
         *
         * @param {*} listType -  scope name
         */
        submit: function (listType) {
            var urlConfirm, urlSubmit, complexTypes, tagNames, names, i, len, tagName, elements, index, elLen, element,
                formData, formDataFields, dataToSend;

            // prepare data
            if (listType) {
                this.current.listType = listType;
                this.current.itemId = null;
            }
            urlConfirm = this.listTypes[this.current.listType].urlConfirm;
            urlSubmit = this.listTypes[this.current.listType].urlSubmit;

            if (!urlConfirm && !urlSubmit && this.dataField) {
                return false;
            }

            if (urlConfirm) {
                this.blockForm.action = urlConfirm;
                this.addFields([new Element('input', {
                    type: 'hidden',
                    name: 'id',
                    value: this.current.itemId
                })]);
            } else {
                this.blockForm.action = urlSubmit;
                complexTypes = this.listTypes[this.current.listType].complexTypes;

                if (complexTypes) {
                    this.addFields([new Element('input', {
                        type: 'hidden',
                        name: 'configure_complex_list_types',
                        value: complexTypes.join(',')
                    })]);
                }

                this._processFieldsData('current_confirmed_to_form');

                // Disable item controls that duplicate added fields (e.g. sometimes qty controls can intersect)
                // so they won't be submitted
                tagNames = ['input', 'select', 'textarea'];

                names = {}; // Map of added field names

                /* eslint-disable max-depth */
                for (i = 0, len = tagNames.length; i < len; i++) {
                    tagName = tagNames[i];
                    elements = this.blockFormAdd.getElementsByTagName(tagName);

                    for (index = 0, elLen = elements.length; index < elLen; index++) {
                        names[elements[index].name] = true;
                    }
                }

                for (i = 0, len = tagNames.length; i < len; i++) {
                    tagName = tagNames[i];
                    elements = this.blockFormConfirmed.getElementsByTagName(tagName);

                    for (index = 0, elLen = elements.length; index < elLen; index++) {
                        element = elements[index];

                        if (names[element.name]) {
                            element.setAttribute('configure_disabled', 1);
                            element.setAttribute('configure_prev_disabled', element.disabled ? 1 : 0);
                            element.disabled = true;
                        } else {
                            element.setAttribute('configure_disabled', 0);
                        }
                    }
                }
            }

            if (Object.isFunction(this.beforeSubmitCallback[this.current.listType])) {
                this.beforeSubmitCallback[this.current.listType]();
            }

            formData = new FormData(this.blockForm);

            if (this.dataField) {
                jQuery('button[data-role="update-quote"]').trigger('remoteQuery', {
                    url: urlSubmit,
                    data: formData
                });
            } else {
                formDataFields =  jQuery(this.blockForm).serializeArray();
                dataToSend = [];

                formDataFields.each(function (el) {
                    if (el.name.indexOf('super_attribute') !== -1 ||
                        el.name.indexOf('options') !== -1 ||
                        el.name.indexOf('bundle_option') !== -1
                    ) {
                        el.name = el.name.replace(/item\[(.+?)\]\[(.+?)\]\[(.+?)\](.*?)/, '$2[$3]$4');
                    } else if (el.name.indexOf('gift') !== -1) {
                        el.name = el.name.replace(/item\[(.+?)\]\[(.+?)\](.*?)/, '$2');
                    }
                    dataToSend.push(el);
                });
                jQuery('#product-' + this.productId).val(jQuery.param(dataToSend)).trigger('change');
            }
            this.blockFormAdd.update();

            return this;
        },

        /**
         * @return {Object}
         */
        onConfirmBtn: function () {
            if (jQuery(this.blockForm).valid()) {
                if (this.listTypes[this.current.listType].urlConfirm) {
                    this.submit();
                } else if (this.dataField) {
                    this._processFieldsData('item_confirm');
                    this._closeWindow();

                    if (Object.isFunction(this.confirmCallback[this.current.listType])) {
                        this.confirmCallback[this.current.listType]();
                    }
                } else {
                    this._processFieldsData('item_confirm');
                    this._closeWindow();

                    if (Object.isFunction(this.confirmCallback[this.current.listType])) {
                        this.confirmCallback[this.current.listType]();
                    }
                    this.submit();
                    $(this.confirmedCurrentId).remove();
                }
            }

            /* eslint-enable max-depth */
            return this;
        },

        /**
         * @param {String} method
         * @private
         */
        _processFieldsData: function (method) {
            /**
             * Internal function for rename fields names of some list type
             * if listType is not specified, then it won't be added as prefix to all names
             *
             * @param {String} m - can be 'current_confirmed_to_form', 'form_confirmed_to_confirmed'
             * @param {*} blockItem
             */
            var _renameFields = function (m, blockItem, listType) {
                var pattern = null,
                    patternFlat = null,
                    replacement = null,
                    replacementFlat = null,
                    scopeArr = blockItem.id.match(/.*\[\w+\]\[([^\]]+)\]$/),
                    itemId = scopeArr[1],
                    stPattern, stPatternFlat, rename;

                if (m === 'current_confirmed_to_form') {
                    pattern = RegExp('(\\w+)(\\[?)');
                    patternFlat = RegExp('(\\w+)');
                    replacement = 'item[' + itemId + '][$1]$2';
                    replacementFlat = 'item_' + itemId + '_$1';

                    if (listType) {
                        replacement = 'list[' + listType + '][item][' + itemId + '][$1]$2';
                        replacementFlat = 'list_' + listType + '_' + replacementFlat;
                    }
                } else if (m === 'form_confirmed_to_confirmed') {
                    stPattern = 'item\\[' + itemId + '\\]\\[(\\w+)\\](.*)';
                    stPatternFlat = 'item_' + itemId + '_(\\w+)';

                    if (listType) {
                        stPattern = 'list\\[' + listType + '\\]\\[item\\]\\[' + itemId + '\\]\\[(\\w+)\\](.*)';
                        stPatternFlat = 'list_' + listType + '_' + stPatternFlat;
                    }
                    pattern = new RegExp(stPattern);
                    patternFlat = new RegExp(stPatternFlat);
                    replacement = '$1$2';
                    replacementFlat = '$1';
                } else {
                    return false;
                }

                /**
                 * @param {Array} elms
                 */
                rename = function (elms) {
                    var i;

                    for (i = 0; i < elms.length; i++) {
                        if (elms[i].name && elms[i].type == 'file') { //eslint-disable-line eqeqeq
                            elms[i].name = elms[i].name.replace(patternFlat, replacementFlat);
                        } else if (elms[i].name) {
                            elms[i].name = elms[i].name.replace(pattern, replacement);
                        }
                    }
                };
                rename(blockItem.getElementsByTagName('input'));
                rename(blockItem.getElementsByTagName('select'));
                rename(blockItem.getElementsByTagName('textarea'));
            },
            mageData, fieldsValue, getConfirmedValues, restoreConfirmedValues, allowedListTypes, listInfo, i,
            len;

            switch (method) {
                case 'item_confirm':
                    if (!$(this.confirmedCurrentId)) {
                        this.blockConfirmed.insert(new Element('div', {
                            id: this.confirmedCurrentId
                        }));
                    } else {
                        $(this.confirmedCurrentId).update();
                        this.blockConfirmed.insert($(this.confirmedCurrentId));
                    }
                    this.blockFormFields.childElements().each(function (elm) {
                        $(this.confirmedCurrentId).insert(elm);
                    }.bind(this));
                    break;

                case 'item_restore':
                    // clone confirmed to form
                    mageData = null;

                    this.blockFormFields.update();
                    $(this.confirmedCurrentId).childElements().each(function (elm) {
                        var cloned = elm.cloneNode(true);

                        if (elm.mageData) {
                            cloned.mageData = elm.mageData;
                            mageData = elm.mageData;
                        }
                        this.blockFormFields.insert(cloned);
                    }.bind(this));

                    // get confirmed values
                    fieldsValue = {};

                    /**
                     * Get confirmed values.
                     */
                    getConfirmedValues = function (elms) {
                        var j;

                        /* eslint-disable */
                        for (j = 0; j < elms.length; j++) {
                            if (elms[j].name) {
                                if (typeof fieldsValue[elms[j].name] === 'undefined') {
                                    fieldsValue[elms[j].name] = {};
                                }

                                if (elms[j].type == 'checkbox') {
                                    fieldsValue[elms[j].name][elms[j].value] = elms[j].checked;
                                } else if (elms[j].type == 'radio') {
                                    if (elms[j].checked) {
                                        fieldsValue[elms[j].name] = elms[j].value;
                                    }
                                } else {
                                    fieldsValue[elms[j].name] = Form.Element.getValue(elms[j]);
                                }
                            }
                        }
                    };
                    getConfirmedValues($(this.confirmedCurrentId).getElementsByTagName('input'));
                    getConfirmedValues($(this.confirmedCurrentId).getElementsByTagName('select'));
                    getConfirmedValues($(this.confirmedCurrentId).getElementsByTagName('textarea'));

                    // restore confirmed values
                    restoreConfirmedValues = function (elms) {
                        for (var i = 0; i < elms.length; i++) {
                            if (typeof fieldsValue[elms[i].name] !== 'undefined') {
                                if (elms[i].type != 'file') {
                                    if (elms[i].type == 'checkbox') {
                                        elms[i].checked = fieldsValue[elms[i].name][elms[i].value];
                                    } else if (elms[i].type == 'radio') {
                                        if (elms[i].value == fieldsValue[elms[i].name]) {
                                            elms[i].checked = true;
                                        }
                                    } else {
                                        elms[i].setValue(fieldsValue[elms[i].name]);
                                    }
                                }
                            }
                        }
                    }.bind(this);

                    restoreConfirmedValues(this.blockFormFields.getElementsByTagName('input'));
                    restoreConfirmedValues(this.blockFormFields.getElementsByTagName('select'));
                    restoreConfirmedValues(this.blockFormFields.getElementsByTagName('textarea'));
                    this._addRequiredAttr(this.blockFormFields);
                    // Execute scripts
                    if (mageData && mageData.scripts) {
                        this.restorePhase = true;

                        try {
                            mageData.scripts.map(function (script) {
                                return eval(script);
                            });
                        } catch (e) {
                            console.log(e);
                        }
                        this.restorePhase = false;
                    }
                    break;

                case 'current_confirmed_to_form':
                    allowedListTypes = {};
                    allowedListTypes[this.current.listType] = true;
                    listInfo = this.listTypes[this.current.listType];

                    if (listInfo.complexTypes) {
                        for (i = 0, len = listInfo.complexTypes.length; i < len; i++) {
                            allowedListTypes[listInfo.complexTypes[i]] = true;
                        }
                    }

                    this.blockFormConfirmed.update();
                    this.blockConfirmed.childElements().each(function (blockItem) {
                        var scopeArr = blockItem.id.match(/.*\[(\w+)\]\[([^\]]+)\]$/),
                            listType = scopeArr[1],
                            itemId = scopeArr[2];

                        if (allowedListTypes[listType] && (!this.itemsFilter[listType] ||
                            this.itemsFilter[listType].indexOf(itemId) != -1)) {
                            _renameFields(method, blockItem, listInfo.complexTypes ? listType : null);
                            this.blockFormConfirmed.insert(blockItem);
                        }
                    }.bind(this));
                    break;

                case 'form_confirmed_to_confirmed':
                    listInfo = this.listTypes[this.current.listType];
                    this.blockFormConfirmed.childElements().each(function (blockItem) {
                        var scopeArr = blockItem.id.match(/.*\[(\w+)\]\[([^\]]+)\]$/),
                            listType = scopeArr[1];

                        _renameFields(method, blockItem, listInfo.complexTypes ? listType : null);
                        this.blockConfirmed.insert(blockItem);
                    }.bind(this));
                    break;

                /* eslint-enable */
            }
        },

        /**
         * @private
         */
        _showWindow: function () {
            jQuery(this.dialog).closest('[data-role="modal"]').addClass('configurable-negotiable-modal');
            this.dialog.modal('openModal');
            //this._toggleSelectsExceptBlock(false);

            if (Object.isFunction(this.showWindowCallback[this.current.listType])) {
                this.showWindowCallback[this.current.listType]();
            }
        },

        /**
         * @param {*} confirm
         * @param {*} productId
         */
        setFieldsData: function (confirm, productId) {
            this.dataField = !!confirm;
            this.productId = productId;
        },

        /**
         * Get configuration fields of product through ajax and show them
         *
         * @param {*} listType - scope name
         * @param {*} itemId
         * @param {*} config
         */
        _requestItemConfiguration: function (listType, itemId, config) {
            var url;

            if (!this.listTypes[listType].urlFetch) {
                return false;
            }
            url = this.listTypes[listType].urlFetch;

            if (url) {
                new Ajax.Request(url, {
                    parameters: {
                        id: itemId,
                        config: config
                    },
                    onSuccess: function (transport) {
                        var response = transport.responseText,
                            mageData, scripts, scriptHolder;

                        if (response.isJSON()) {
                            response = response.evalJSON();

                            if (response.error) {
                                this.blockMsg.show();
                                this.blockMsgError.innerHTML = response.message;
                                this.blockCancelBtn.hide();
                                this.setConfirmCallback(listType, null);
                                this._showWindow();
                            }
                        } else if (response) {
                            response += '';
                            this.blockFormFields.update(response);

                            // Add special div to hold mage data, e.g. scripts to execute on every popup show
                            mageData = {};
                            scripts = response.extractScripts();
                            mageData.scripts = scripts;

                            scriptHolder = new Element('div', {
                                'style': 'display:none'
                            });
                            scriptHolder.mageData = mageData;
                            this.blockFormFields.insert(scriptHolder);
                            this._addRequiredAttr(this.blockFormFields);
                            jQuery(this.blockFormFields).trigger('contentUpdated');
                            // Show window
                            this._showWindow();
                        }
                    }.bind(this)
                });
            }
        },

        /**
         * Add required attribute for radio and checkbox
         *
         * @param {HTMLElement} el
         */
        _addRequiredAttr: function (el) {

            var requiredBlocks = jQuery(el).find('div.required .nested');

            if (!jQuery.validator.methods.requiredCheckbox) {
                jQuery.validator.addMethod('requiredCheckbox', function (value, element) {
                    var elementsId = jQuery(element).data('id'),
                        wrapper = jQuery('.product-composite-configure-inner');

                    return wrapper.find('[data-id=' + elementsId + ']:checkbox:checked').length;
                }, jQuery.validator.format('Please select an option.'));
            }

            requiredBlocks.each(function (i, elem) {
                var withCheckbox = jQuery(elem).find('input[type="checkbox"]'),
                    withRadio = jQuery(elem).find('input[type="radio"]');

                if (withCheckbox.length) {
                    withCheckbox.attr('data-id', 'checkbox-' + i);
                    withCheckbox.rules('add', {
                        requiredCheckbox: true
                    });
                } else if (withRadio.length) {
                    withRadio.rules('add', {
                        required: true,
                        messages: {
                            required: 'Please select an option.'
                        }
                    });
                }
            });
        }
    };

    jQuery.extend(true, window.productConfigure, productConfigure);
});
