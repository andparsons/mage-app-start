import {path} from './path';
import {themes} from './themes';

export const collector = themeName => {
  return `php -dmemory_limit=-1 bin/magento dev:source-theme:deploy ${themes[themeName].files.join(' ')} --type=less --locale=${themes[themeName].locale} --area=${themes[themeName].area} --theme=${themes[themeName].name}`;
};

export const autopath = (themeName, folder) => folder +
  themes[themeName].area + '/' +
  themes[themeName].name + '/' +
  themes[themeName].locale + '/';

export const lessFiles = themeName => {
  let lessStringArray = [],
    cssStringArray = [],
    lessFiles = {},
    i = 0;

  for (i; i < themes[themeName].files.length; i++) {
    cssStringArray[i] = path.pub +
      themes[themeName].area + '/' +
      themes[themeName].name + '/' +
      themes[themeName].locale + '/' +
      themes[themeName].files[i] + '.css';

    lessStringArray[i] = path.pub +
      themes[themeName].area + '/' +
      themes[themeName].name + '/' +
      themes[themeName].locale + '/' +
      themes[themeName].files[i] + '.less';

    lessFiles[cssStringArray[i]] = lessStringArray[i];
  }

  return lessFiles;
};

export const combo = {collector, autopath, lessFiles};
module.exports = combo;
