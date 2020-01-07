import {each, extend} from 'underscore';
import {lessFiles} from './conf/combo';
import {themes} from './conf/themes';

const lessOptions = {
  /**
   * @type {Less.Options}
   */
  options: {
    sourceMap: true,
    sourceMapURL: (destFile) => {
      return '/' + destFile + '.map'
    },
    sourceMapBasepath: (srcFile) => {
      return process.cwd() + '/' + srcFile
    },
    sourceMapRootpath: 'phpstorm://open?file=//' + process.cwd(),
    outputSourceFiles: true,
    sourceMapFileInline: false,
    compress: false,
    lint: true,
    strictImports: false,
    insecure: true,
    depends: true,
    color: true,
    ieCompat: false,
    javascriptEnabled: false,
    dumpLineNumbers: false, // use 'comments' instead false to output line comments for source
    rootpath: '',
    silent: false,
    syncImport: true,
  }
};
let themeOptions = {};
each(themes, (theme, name) => {
  themeOptions[name] = {
    files: lessFiles(name)
  };
});
/**
 * Compiles Less to CSS and generates necessary files if requested.
 */
export const less = extend(themeOptions, lessOptions);
module.exports = less;
