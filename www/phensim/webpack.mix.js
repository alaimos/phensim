const { mix } = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.styles('resources/assets/css/*', 'public/css/all.css').sourceMaps()
   .sass('resources/assets/sass/app.scss', 'public/css/app.css')
   .scripts('resources/assets/js/core/*', 'public/js/core.js').sourceMaps()
   .js('resources/assets/js/app.js', 'public/js/app.js');