import {readdirSync} from "fs";

const getDirectories = (source = 'app/design/frontend/Sozo') =>
  readdirSync(source, {withFileTypes: true})
    .filter(dirent => dirent.isDirectory())
    .map(dirent => dirent.name);
export const themes = {
  blank: {
    area: 'frontend',
    name: 'Magento/blank',
    locale: 'en_GB',
    files: [
      'css/styles-m',
      'css/styles-l'
    ],
    dsl: 'less'
  },
  luma: {
    area: 'frontend',
    name: 'Magento/luma',
    locale: 'en_GB',
    files: [
      'css/styles-m',
      'css/styles-l'
    ],
    dsl: 'less'
  },
  backend: {
    area: 'adminhtml',
    name: 'Magento/backend',
    locale: 'en_GB',
    files: [
      'css/styles-old',
      'css/styles'
    ],
    dsl: 'less'
  }
};
