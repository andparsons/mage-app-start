/*eslint-disable */
define([], function () {
  var ConfigModifier =
  /*#__PURE__*/
  function () {
    "use strict";

    function ConfigModifier() {}

    var _proto = ConfigModifier.prototype;

    /**
     * Initialize the config
     *
     * @param {String} contentTypeId
     * @param {Object} config
     */
    _proto.modify = function modify(contentTypeId, config) {
      if (config.adapter_config.mode === "inline") {
        config.adapter.settings.fixed_toolbar_container = "#" + contentTypeId + " " + config.adapter.settings.fixed_toolbar_container;
      }
    };

    return ConfigModifier;
  }();

  return ConfigModifier;
});
//# sourceMappingURL=config-modifier.js.map
