import load from "load-grunt-config";
import {join, resolve} from "path";

const configDir = './grunt/';
const configPath = join(__dirname, configDir);
module.exports = (grunt) => {
  // get('themes');
  load(grunt, {
    configPath,
    init: true,
  });
  grunt.registerTask('default', []);
  grunt.registerTask('refresh', ['clean', 'exec:all', 'less']);
  process.chdir(resolve("./"));
};
