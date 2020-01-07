/*eslint-disable */
define([], function () {

  /**
   * Delay until a condition is met
   *
   * @param {() => void} callback
   * @param {() => boolean} condition
   * @param {number} interval
   */
  function delayUntil(callback, condition, interval) {
    if (interval === void 0) {
      interval = 50;
    }

    var delayInterval = setInterval(function () {
      if (condition()) {
        clearInterval(delayInterval);
        callback();
      }
    }, interval);
  }

  return delayUntil;
});
//# sourceMappingURL=delay-until.js.map
