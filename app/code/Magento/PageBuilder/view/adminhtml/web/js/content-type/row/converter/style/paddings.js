/*eslint-disable */
define(["underscore", "Magento_PageBuilder/js/utils/object"], function (_underscore, _object) {
  var Paddings =
  /*#__PURE__*/
  function () {
    "use strict";

    function Paddings() {}

    var _proto = Paddings.prototype;

    /**
     * @param {string} value
     * @returns {Object | string}
     */
    _proto.fromDom = function fromDom(value) {
      return value;
    }
    /**
     * Convert value to knockout format
     *
     * @param {string} name
     * @param {DataObject} data
     * @returns {string | object}
     */
    ;

    _proto.toDom = function toDom(name, data) {
      var result = {};
      var value = (0, _object.get)(data, name);

      if (value && _underscore.isString(value)) {
        value = JSON.parse(value);
      }

      if (value && undefined !== value.padding) {
        result.paddingLeft = value.padding.left ? value.padding.left + "px" : "";
        result.paddingTop = value.padding.top ? value.padding.top + "px" : "";
        result.paddingRight = value.padding.right ? value.padding.right + "px" : "";
        result.paddingBottom = value.padding.bottom ? value.padding.bottom + "px" : "";
      }

      return result;
    };

    return Paddings;
  }();

  return Paddings;
});
//# sourceMappingURL=paddings.js.map
