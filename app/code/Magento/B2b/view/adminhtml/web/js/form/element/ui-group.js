/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'underscore',
    'jquery',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Ui/js/lib/key-codes',
    'mage/translate'
], function (ko, _, $, Abstract, keyCodes, $t) {
    'use strict';

    /**
     * Preprocessing value
     *
     * @param {Array|String|Number} value - Options value
     *
     * @return {Array} value reformed to array
     */
    function convertValue(value) {
        if (_.isArray(value)) {
            return value;
        }

        if (_.isNumber(value)) {
            return value.toString().split();
        }

        if (_.isString(value) && value) {
            return value.split();
        }

        return [];
    }

    /**
     * Convert option value to string
     *
     * @param {Object} option
     *
     * @return {Object} option with converted value
     */
    function convertOptionsValue(option) {
        option.value = option.value.toString();

        return option;
    }

    /**
     * Check options value type
     *
     * @param {Array} optionsGroup
     * @param {Boolean} condition - multi or single select
     *
     * @return {Array} return groups or options with correct value
     */
    function checkOptions(optionsGroup, condition) {
        return optionsGroup.map(function (group) {

            if (!condition) {
                return _.isString(group.value) ? group : convertOptionsValue(group);
            }
            group.value = group.value.map(function (option) {
                return _.isString(option.value) ? option : convertOptionsValue(option);
            });

            return group;
        });
    }

    return Abstract.extend({

        defaults: {
            listVisible: false,
            options: [],
            convertedOptions: [],
            showCheckbox: false,
            renderOptionsAfterOpen: true,
            isOptionRendered: false,
            searchFocus: false,
            checkAll: false,
            multiple: false,
            group: true,
            isSearchActive: false,
            isResult: false,
            resultLabel: $t('Please Select'),
            quantitySearchItems: '0',
            hoverClass: '_hover',
            valueAttr: 'data-value',
            wrapDataAttr: 'wrap-ui-group',
            filterInputValue: '',
            hasFocus: false,
            cacheOptionsGroup: [],
            filteredOptions: [],
            cacheOptions: [],
            cacheOptionsValue: [],
            selected: [],
            elementTmpl: 'Magento_B2b/form/element/ui-group'
        },

        /**
         * Initializes UISelect component.
         *
         * @returns {UISelect} Chainable.
         */
        initialize: function () {
            this._super()
                .setGroupParam()
                .setCacheOptions();

            return this;
        },

        /**
         * Calls 'initObservable' of parent, initializes 'options' and 'initialOptions'
         *     properties, calls 'setOptions' passing options to it
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super().observe([
                'filterInputValue',
                'options',
                'convertedOptions',
                'resultLabel',
                'listVisible',
                'quantitySearchItems',
                'placeholderVisible',
                'hasFocus',
                'selected',
                'isSearchActive',
                'searchFocus',
                'filteredOptions',
                'isResult'
            ]);

            return this;
        },

        /**
         * @inheritdoc
         */
        reset: function () {
            var selected = this.getSelected();

            if (this.cacheOptions.length > 0 && this.formElement === 'select') {
                this.cacheOptions.forEach(function (elem) {
                    if (elem.value === this.initialValue) {
                        selected = elem;
                    }
                }, this);
                this.resultLabel(selected.label);
                this.selected(convertValue(selected.value));
            } else {
                this.value(this.initialValue);
            }
            this.error(false);

            return this;
        },

        /**
         * Sets group property value
         * based on the type of 'options' value.
         *
         * @returns {Object} Chainable
         */
        setGroupParam: function () {
            if (!_.isEmpty(this.options())) {
                this.group = _.isArray(this.options()[0].value);
            }

            return this;
        },

        /**
         * Set option to cache array.
         *
         * @returns {Object} Chainable
         */
        setCacheOptions: function () {
            this.selected(convertValue(this.value()));
            this.cacheOptionsGroup = this.options();

            if (this.group) {
                this.cacheOptions = this.cacheOptionsGroup.reduce(function (options, optGroup) {
                    return options.concat(optGroup.value);
                }, []);
            } else {
                this.cacheOptions = this.cacheOptionsGroup;
            }
            this.cacheOptionsValue = this.cacheOptions.map(function (item) {
                return item.value;
            });
            this.renderSelectedOptions();

            if (!this.renderOptionsAfterOpen) {
                this.renderOptions();
            }

            return this;
        },

        /**
         * Render options.
         */
        renderOptions: function () {
            this.convertedOptions(checkOptions(this.options(), this.group));
            this.isOptionRendered = true;
        },

        /**
         * Render selected option.
         *
         * @returns {Object} Chainable
         */
        renderSelectedOptions: function () {
            var selected = this.getSelected();

            if (!this.multiple && selected.length) {
                this.resultLabel(selected[0].label);
            }

            return this;
        },

        /**
         * Selected all options.
         */
        selectAll: function () {
            if (this.getFilteredOptionsQuantity()) {
                this.selected(_.union(this.selected(), this.getFilteredOptionsValues()));

                return false;
            }
            this.selected(this.cacheOptionsValue);
        },

        /**
         * Deselected all options.
         */
        deselectAll: function () {
            if (this.getFilteredOptionsQuantity()) {
                this.selected(_.difference(this.selected(), this.getFilteredOptionsValues()));

                return false;
            }
            this.selected([]);
        },

        /**
         * Check availability selected options.
         *
         * @returns {Boolean}
         */
        hasData: function () {
            return !!this.getSelectedQuantity();
        },

        /**
         * Get quantity selected options.
         *
         * @returns {Number}
         */
        getSelectedQuantity: function () {
            return this.selected().length;
        },

        /**
         * Get label with quantity selected options.
         *
         * @returns {String}
         */
        getLabelSelectedQuantity: function () {
            var phrase = $t('%s of %s selected'),
                quantitySelectedOptions = this.isResult() ? this.getSelected(this.filteredOptions()).length :
                    this.getSelectedQuantity();

            return phrase.replace('%s', quantitySelectedOptions)
                .replace('%s', this.getFilteredOptionsQuantity() || this.getOptionsQuantity());
        },

        /**
         * Get quantity options.
         *
         * @returns {Number}
         */
        getOptionsQuantity: function () {
            return this.cacheOptions.length;
        },

        /**
         * Get filtered options quantity.
         *
         * @returns {Number}
         */
        getFilteredOptionsQuantity: function () {
            return this.filteredOptions().length;
        },

        /**
         * Get filtered options values.
         *
         * @returns {Number}
         */
        getFilteredOptionsValues: function () {
            return this.filteredOptions().map(function (item) {
                return item.value;
            });
        },

        /**
         * Set all value of selected options to source.
         */
        applyChange: function () {
            this.setValue(this.selected());
        },

        /**
         * object with key - keyname and value - handler function for this key
         *
         * @returns {Object} Object with handlers function name.
         */
        keyDownHandlers: function () {
            return {
                enterKey: this.enterKeyHandler,
                escapeKey: this.escapeKeyHandler,
                pageUpKey: this.pageUpKeyHandler,
                pageDownKey: this.pageDownKeyHandler,
                spaceKey: this.spaceKeyHandler
            };
        },

        /**
         * Handler keydown event to filter options input
         *
         *
         * @param {Object} ui - class
         * @param {Object} e - event
         * @returns {Boolean} Returned true for emersion events
         */
        filterOptionsKeydown: function (ui, e) {
            var isGroup = this.group,
                value = e ? e.target.value : false,
                quantitySearchItems,
                groupsOptions,
                options,
                newGroupOptions,
                newOptions;

            if (e && this.isArrowKey(keyCodes[e.keyCode])) {
                return true;
            }

            if (value && value.length > 2) {
                this.isSearchActive(!!value.length);
                groupsOptions = this.cacheOptionsGroup.map(function (groupOption) {

                    if (!isGroup) {
                        return groupOption.label.toLowerCase().indexOf(value.toLowerCase(), 0) !== -1 ?
                            groupOption :
                            false;
                    }
                    newGroupOptions = {};
                    newOptions = groupOption.value.filter(function (option) {
                        return option.label.toLowerCase().indexOf(value.toLowerCase(), 0) !== -1;
                    });
                    options = _.union(options, newOptions);

                    if (newOptions.length) {
                        _.extend(newGroupOptions, groupOption);
                        newGroupOptions.value = newOptions;

                        return newGroupOptions;
                    }
                });

                groupsOptions = _.compact(groupsOptions);
            }

            if (!isGroup && groupsOptions) {
                quantitySearchItems = groupsOptions.length;
            }

            if (options) {
                quantitySearchItems = options.length;
            }
            this.isResult(!!groupsOptions);
            this.filteredOptions(options || []);
            this.convertedOptions(groupsOptions || this.cacheOptionsGroup);
            this.setQuantityItems(quantitySearchItems);
            this.setElements();

            return true;
        },

        /**
         * Set actual quantity items
         *
         * @param {Array} quantityItems
         */
        setQuantityItems: function (quantityItems) {
            _.isUndefined(quantityItems) ? this.quantitySearchItems(this.getOptionsQuantity()) :
                this.quantitySearchItems(quantityItems.toString());
        },

        /**
         * Cancel last change
         */
        cancelChange: function () {
            this.selected(convertValue(this.initialValue));
            this.applyChange();
        },

        /**
         * Clear search field
         */
        clearSearch: function () {
            this.filterInputValue('');
            this.filterOptionsKeydown();
            this.isSearchActive(false);
        },

        /**
         * Set value to source
         *
         * @param {Array} value - selected value
         */
        setValue: function (value) {
            this.multiple ? this.value(value) : this.value(value.join());
            this.listVisible(false);
            this.renderSelectedOptions();
            this.resetOptions();
        },

        /**
         * Check selected option.
         *
         * @param {String} value - option value
         * @return {Boolean}
         */
        isSelected: function (value) {
            var selected = this.selected();

            return _.contains(selected, value);
        },

        /**
         * Set selected option in dropdown list
         *
         * @param {String} value - option value
         */
        setSelected: function (value) {
            var selectedValue = this.selected(),
                currentValue = [];

            if (this.multiple) {
                if (_.contains(selectedValue, value)) {
                    this.selected(_.without(selectedValue, value));
                } else {
                    currentValue.push(value);
                    this.selected(_.union(selectedValue, currentValue));
                }
            } else {
                selectedValue.splice(0, 1, value);
                this.selected(selectedValue);
            }

            if (!this.multiple) {
                this.applyChange();
            }
        },

        /**
         * Remove selected option.
         *
         * @param {String} value - option value
         * @param {Object} option - option data
         * @param {Object} e - event
         */
        removeSelected: function (value, option, e) {
            e.stopPropagation();
            this.setSelected(value);

            if (!this.listVisible()) {
                this.applyChange();
            }
        },

        /**
         * Get selected elements
         *
         * @param {Array} opt - options
         * @returns {Array} array labels
         */
        getSelected: function (opt) {
            var selectedValue = this.selected(),
                options = opt || this.cacheOptions;

            return options.filter(function (option) {
                return _.contains(selectedValue, option.value.toString());
            });
        },

        /**
         * Handler outerClick event. Closed options list
         */
        outerClick: function () {
            if (!this.multiple) {
                this.listVisible() ? this.listVisible(false).resetOptions() : false;
            }
        },

        /**
         * Remove hover property
         */
        resetHover: function () {
            if (this.elementOptions) {
                this.elementOptions.removeClass(this.hoverClass);
                this.direction = -1;
            }
        },

        /**
         * Callback for focus in of dropdown
         */
        onFocusIn: function () {
            this.hasFocus(true);
        },

        /**
         * Callback for focus out of dropdown
         */
        onFocusOut: function () {
            this.hasFocus(false);
        },

        /**
         * Callback for toggle visible of list dropdown
         *
         * @param {Object} ui - ui class
         * @param {Object} e - event
         */
        toggleListVisible: function (ui, e) {
            var isVisibleList = this.listVisible();

            if (this.renderOptionsAfterOpen && !this.isOptionRendered) {
                this.renderOptions();
            }

            if (e && this.multiple && isVisibleList) {
                return false;
            }
            this.listVisible(!isVisibleList);
            this.searchFocus(!this.searchFocus());

            if (!isVisibleList) {
                this.resetOptions();
                this.quantitySearchItems(this.getOptionsQuantity());
            }

            if (!this.element) {
                this.setElements(e);
            }
        },

        /**
         * Set DOM element to cache
         *
         * @param {Object} e - event
         */
        setElements: function (e) {
            this.element = this.element || $(e.target);

            if (this.element.attr('data-role') !== this.wrapDataAttr) {
                this.element = this.element.closest('[data-role="wrap-ui-group"]');
            }

            this.elementOptions = this.element.find('[data-role="option"]');
        },

        /**
         * Reset all change of options
         */
        resetOptions: function () {
            this.filterInputValue('');
            this.convertedOptions(this.cacheOptionsGroup);
            this.filteredOptions([]);
            this.temporaryValue = null;
            this.resetHover();
            this.isSearchActive(false);
            this.isResult(false);
        },

        /**
         * Checked key name
         *
         * @param {String} keyName
         * @returns {Boolean}
         */
        isArrowKey: function (keyName) {
            return keyName === 'pageDownKey' || keyName === 'pageUpKey';
        },

        /**
         * Switcher to parse keydown event and delegate event to needful method
         *
         * @param {Object} data - element data
         * @param {Object} e - keydown event
         * @returns {Boolean} if handler for this event doesn't found return true
         */
        keydownSwitcher: function (data, e) {
            var keyName = keyCodes[e.keyCode];

            if (this.keyDownHandlers().hasOwnProperty(keyName)) {
                this.keyDownHandlers()[keyName].apply(this, arguments);
            }

            if (this.isArrowKey(keyName)) {
                e.preventDefault();
            }

            return true;

        },

        /**
         * Handler enter key, if select list is closed - open select,
         * if select list is open toggle selected current option
         *
         * @param {Object} data - element data
         * @param {Object} e - keydown event
         */
        enterKeyHandler: function (data, e) {
            var value = this.temporaryValue || $(e.target).attr(this.valueAttr);

            this.element.focus();

            if (!this.multiple && value) {
                this.setSelected(this.temporaryValue);
            }

            if (value) {
                this.applyChange();

                return false;
            }

            this.toggleListVisible(data, e);
        },

        /**
         * Handler escape key, if select list is open - closes it
         */
        escapeKeyHandler: function () {
            this.element.focus();
            this.listVisible(false);

            if (this.multiple) {
                this.cancelChange();
            }
        },

        /**
         * Set hover to visible element
         *
         * @param {Number} direction - iterator
         */
        setHoverToElement: function (direction) {
            var elementsLength = this.elementOptions.length;

            this.elementOptions.removeClass(this.hoverClass);

            if (_.isUndefined(this.direction)) {
                this.direction = -1;
            }

            this.direction += direction;

            if (this.direction > elementsLength - 1) {
                this.direction = 0;
            }

            if (this.direction < 0) {
                this.direction = elementsLength - 1;
            }

            this.temporaryValue = $(this.elementOptions[this.direction])
                .addClass(this.hoverClass)
                .focus()
                .attr(this.valueAttr);
        },

        /**
         * Handler pageUp key, selected previous option in list, if current option is first -
         * selected last option in list
         *
         * @param {Object} data - element data
         * @param {Object} e - keydown event
         */
        pageUpKeyHandler: function (data, e) {
            e.stopPropagation();
            this.setHoverToElement(-1);
        },

        /**
         * Handler pageDown key, selected next option in list, if current option is last
         * selected first option in list
         *
         * @param {Object} data - element data
         * @param {Object} e - keydown event
         */
        pageDownKeyHandler: function (data, e) {
            e.stopPropagation();
            this.setHoverToElement(1);
        },

        /**
         * Handler space key, selected current option in list, if current option is selected
         * this option is deselected
         *
         * @param {Object} data - element data
         * @param {Object} e - keydown event
         */
        spaceKeyHandler: function (data, e) {
            if (!this.searchFocus()) {
                e.stopPropagation();
                e.preventDefault();
                this.setSelected($(e.target).data('value').toString());
            }
        }
    });
});
