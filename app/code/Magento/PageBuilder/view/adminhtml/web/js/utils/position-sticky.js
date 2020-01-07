/*eslint-disable */
define([], function () {

  /**
   * Determine if the current browser supports position sticky
   *
   * @returns {boolean}
   */
  function supportsPositionSticky() {
    if (!window.getComputedStyle) {
      return false;
    }

    var testNode = document.createElement("div");
    return ["", "-webkit-", "-moz-", "-ms-"].some(function (prefix) {
      try {
        testNode.style.position = prefix + "sticky";
      } catch (e) {// Fail silently
      }

      return testNode.style.position !== "";
    });
  }

  return {
    supportsPositionSticky: supportsPositionSticky
  };
});
//# sourceMappingURL=position-sticky.js.map
