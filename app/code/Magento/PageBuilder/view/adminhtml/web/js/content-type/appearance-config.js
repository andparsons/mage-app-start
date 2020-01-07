/*eslint-disable */
define(["Magento_PageBuilder/js/config"], function (_config) {

  /**
   * Get config for appearance
   *
   * @param {string} contentType
   * @param {string} appearance
   * @returns {ContentTypeConfigAppearanceInterface}
   * @api
   */
  function getAppearanceConfig(contentType, appearance) {
    var contentTypeConfig = _config.getContentTypeConfig(contentType);

    var config;

    if (contentTypeConfig.appearances !== undefined) {
      if (!appearance) {
        for (var key in contentTypeConfig.appearances) {
          if (!!contentTypeConfig.appearances[key].default) {
            appearance = key;
            break;
          }
        }
      }

      if (appearance) {
        config = contentTypeConfig.appearances[appearance];
      }
    }

    return config;
  }

  return getAppearanceConfig;
});
//# sourceMappingURL=appearance-config.js.map
