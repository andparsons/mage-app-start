/*eslint-disable */
define([], function () {

  /**
   * The frame does not need to create or observe events, however the app will naturally attempt to. So let's stop that!
   */
  var _default = {
    on: function on() {
      return this;
    },
    off: function off() {
      return this;
    },
    trigger: function trigger() {
      return this;
    }
  };
  return _default;
});
//# sourceMappingURL=events.js.map
