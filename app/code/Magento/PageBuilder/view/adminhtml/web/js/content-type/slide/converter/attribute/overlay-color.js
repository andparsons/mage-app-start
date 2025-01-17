/*eslint-disable */
define([], function () {
  var OverlayColor =
  /*#__PURE__*/
  function () {
    "use strict";

    function OverlayColor() {}

    var _proto = OverlayColor.prototype;

    /**
     * Convert value to internal format
     *
     * @param value string
     * @returns {string | object}
     */
    _proto.fromDom = function fromDom(value) {
      return value === "transparent" ? "" : value;
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
      if (data.overlay_color) {
        return data.overlay_color.toString();
      }

      return "";
    };

    return OverlayColor;
  }();

  return OverlayColor;
});
//# sourceMappingURL=overlay-color.js.map
