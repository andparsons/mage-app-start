import svgo from 'imagemin-svgo';

/**
 * @var {imageminOptipng.Options}
 */
const pngOptions = {
  optimizationLevel: 7,
}
/**
 * @var {imageminJpegtran.Options}
 */
const jpgOptions = {
  progressive: true,
}
/**
 * @var {imageminGifsicle.Options}
 */
const gifOptions = {
  interlaced: true
}
/**
 * @var {imageminSvgo.Options}
 */
const svgOptions = {
  use: [svgo()]
}

function imgFiles(type, options) {
  return {
    options,
    files: [{
      expand: true,
      src: ['**/*.' + type],
      ext: '.' + type
    }]
  }
}

export const imageMin = {
  png: imgFiles('png', pngOptions),
  jpg: imgFiles('jpg', jpgOptions),
  gif: imgFiles('gif', gifOptions),
  svg: imgFiles('svg', svgOptions),
};
module.exports = imageMin;
