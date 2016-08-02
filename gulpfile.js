var elixir = require('laravel-elixir');

require('laravel-elixir-vueify');

elixir.config.css.sass.folder = 'scss';
elixir.config.js.outputFolder = 'assets/js';
elixir.config.js.browserify.watchify.options.poll = true;
elixir.config.css.cssnano.pluginOptions.zindex = false;

elixir(function(mix) {
    mix.sass('style.scss', 'public/assets/css')
        .less('AdminLTE.less', 'public/assets/css')
        .browserify(['polyfills.js', 'admin.js'], 'public/assets/js/admin.js')
        .browserify(['polyfills.js', 'public.js'], 'public/assets/js/public.js');
});
