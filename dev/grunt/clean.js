import {each, extend} from 'underscore';
import {autopath} from './conf/combo';
import {path} from './conf/path';
import {themes} from './conf/themes';

const cleanOptions = {
  var: {
    force: true,
    files: [
      {
        force: true,
        dot: true,
        src: [
          path.tmp + "/cache/**/*",
          path.tmp + "/generation/**/*",
          path.tmp + "/log/**/*",
          path.tmp + "/maps/**/*",
          path.tmp + "/page_cache/**/*",
          path.tmp + "/tmp/**/*",
          path.tmp + "/view/**/*",
          path.tmp + "/view_preprocessed/**/*"
        ]
      }
    ]
  },
  pub: {
    force: true,
    files: [
      {
        force: true,
        dot: true,
        src: [
          path.pub + "frontend/**/*",
          path.pub + "adminhtml/**/*",
          path.deployedVersion
        ]
      }
    ]
  },
  styles: {
    force: true,
    files: [
      {
        force: true,
        dot: true,
        src: [
          path.tmp + "/view_preprocessed/**/*",
          path.tmp + "/cache/**/*",
          path.pub + "frontend/**/*.less",
          path.pub + "frontend/**/*.css",
          path.pub + "adminhtml/**/*.less",
          path.pub + "adminhtml/**/*.css",
          path.deployedVersion
        ]
      }
    ]
  },
  markup: {
    force: true,
    files: [
      {
        force: true,
        dot: true,
        src: [
          path.tmp + "/cache/**/*",
          path.tmp + "/generation/**/*",
          path.tmp + "/view_preprocessed/html/**/*",
          path.tmp + "/page_cache/**/*"
        ]
      }
    ]
  },
  js: {
    force: true,
    files: [
      {
        force: true,
        dot: true,
        src: [
          path.pub + "**/*.js",
          path.pub + "**/*.html",
          path.pub + "_requirejs/**/*",
          path.deployedVersion
        ]
      }
    ]
  }
};
let themeOptions = {};
each(themes, (theme, name) => {
  themeOptions[name] = {
    force: true,
    files: [
      {
        force: true,
        dot: true,
        src: [
          path.tmp + "/cache/**/*",
          autopath(name, path.pub) + "**/*",
          autopath(name, path.tmpLess) + "**/*",
          autopath(name, path.tmpSource) + "**/*",
          path.deployedVersion
        ]
      }
    ]
  };
});
export const clean = extend(cleanOptions, themeOptions);
module.exports = clean;

