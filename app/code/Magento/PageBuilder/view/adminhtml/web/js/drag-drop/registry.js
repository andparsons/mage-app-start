/*eslint-disable */
define([], function () {
  var draggedContentTypeConfig;
  /**
   * Set the current dragged blocks config into the registry
   *
   * @param {ContentTypeConfigInterface} config
   */

  function setDraggedContentTypeConfig(config) {
    draggedContentTypeConfig = config;
  }
  /**
   * Retrieve the dragged blocks config
   *
   * @returns {ContentTypeConfigInterface}
   */


  function getDraggedContentTypeConfig() {
    return draggedContentTypeConfig;
  }

  return {
    setDraggedContentTypeConfig: setDraggedContentTypeConfig,
    getDraggedContentTypeConfig: getDraggedContentTypeConfig
  };
});
//# sourceMappingURL=registry.js.map
