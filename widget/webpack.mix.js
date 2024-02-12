let mix = require('laravel-mix');

const publicPath = process.env.PUBLIC_PATH || './';
const fontPath = process.env.FONT_PATH || 'build/fonts';
const sassPath = process.env.SASS_PATH || 'build/css/forumpay_widget.css';
const jsPath = process.env.JS_PATH || 'build/js/forumpay_widget.js';


mix.copy('src/assets/fonts', publicPath + fontPath);
mix.setPublicPath(publicPath).sass('src/assets/scss/main.scss', sassPath, {
  sassOptions: {
    outputStyle: 'expanded'
  }
}).options({
  processCssUrls: false
});
mix.setPublicPath(publicPath).js('src/main.js', jsPath).vue();
