/*eslint-disable */
define([], function () {

  /**
   * @api
   */
  var AttributeReader =
  /*#__PURE__*/
  function () {
    "use strict";

    function AttributeReader() {}

    var _proto = AttributeReader.prototype;

    /**
     * Read attribute from element
     *
     * @param {HTMLElement} element
     * @param {string} source
     * @returns {string | object}
     */
    _proto.read = function read(element, source) {
      return element.getAttribute(source);
    };

    return AttributeReader;
  }();

  return AttributeReader;
});
//# sourceMappingURL=attribute-reader.js.map
