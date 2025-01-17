/*eslint-disable */

function _inheritsLoose(subClass, superClass) { subClass.prototype = Object.create(superClass.prototype); subClass.prototype.constructor = subClass; subClass.__proto__ = superClass; }

define(["Magento_PageBuilder/js/content-type-menu/option"], function (_option) {
  var ConditionalRemoveOption =
  /*#__PURE__*/
  function (_option2) {
    "use strict";

    _inheritsLoose(ConditionalRemoveOption, _option2);

    /**
     * @param {OptionConfigInterface} config
     */
    function ConditionalRemoveOption(config) {
      var _this;

      _this = _option2.call(this, config) || this;
      var parentContentType = _this.preview.contentType.parentContentType;

      if (parentContentType.children().length < 2) {
        _this.isDisabled(true);
      }

      parentContentType.children.subscribe(function (children) {
        _this.isDisabled(children.length < 2);
      });
      return _this;
    }

    return ConditionalRemoveOption;
  }(_option);

  return ConditionalRemoveOption;
});
//# sourceMappingURL=conditional-remove-option.js.map
