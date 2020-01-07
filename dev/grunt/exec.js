import {each, extend, map} from 'underscore';
import combo from './conf/combo';
import {themes} from './conf/themes';

const execOptions = {
  all: {
    cmd: () => {
      const cmdPlus = /^win/.test(process.platform) ? '&' : '&&';

      return map(themes, (theme, name) => combo.collector(name)).join(cmdPlus);
    }
  }
};
let themeOptions = {};

each(themes, (theme, name) => {
  themeOptions[name] = {
    cmd: combo.collector.bind(combo, name)
  };
});

/**
 * Execution into cmd
 */
export const exec = extend(themeOptions, execOptions);
module.exports = exec;
