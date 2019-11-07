let mix = require('laravel-mix');

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

mix.webpackConfig({
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/assets/js')
        }
    }
});

mix.sass('resources/assets/sass/styles.sass', 'public/css')
    .js('resources/assets/js/loan-form.js', 'public/js')
    .js('resources/assets/js/work-history.js', 'public/js')
    .js('resources/assets/js/app.js', 'public/js')
    .js('resources/assets/js/frontend/frontend.js', 'public/frontend/js')
    .js('resources/assets/js/loan-apply.js', 'public/js')
;

