var elixir = require('laravel-elixir');

require('laravel-elixir-vueify');

elixir.config.css.sass.folder = 'scss';
elixir.config.js.outputFolder = 'assets/js';
elixir.config.js.browserify.watchify.options.poll = true;

elixir(function(mix) {
    mix.sass('style.scss', 'public/assets/css')
        .less('AdminLTE.less', 'public/assets/css')
        .browserify('admin.js');
});
