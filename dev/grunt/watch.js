import {each, extend} from 'underscore';
import {autopath} from './conf/combo';
import {path} from './conf/path';
import {themes} from './conf/themes';

const watchOptions = {
  reload: {
    files: path.pub + '**/*.css',
    options: {
      livereload: true
    }
  }
};
let themeOptions = {};
each(themes, (theme, name) => {
  themeOptions[name] = {
    files: [autopath(name, path.pub) + '**/*.less'],
    tasks: 'less:' + name
  };
});
export const watch = extend(themeOptions, watchOptions);
module.exports = watch;
