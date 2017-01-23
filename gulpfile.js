var elixir = require('laravel-elixir');
var autoprefixer = require('gulp-autoprefixer');
var shell = require("gulp-shell");

elixir.config.css.autoprefix = {
    enabled: false,
    options: {
        browsers: ['last 5 versions', '> 1%'],
        cascade: true,
        remove: false
    }
};

var addSassTask = function (src, output, options, useRuby) {
    return compile({
        compiler: 'Sass',
        plugin: useRuby ? 'gulp-ruby-sass' : 'sass',
        pluginOptions: buildOptions(options, useRuby),
        src: src,
        output: output || elixir.config.cssOutput,
        search: '**/*.+(sass|scss)'
    });
};

elixir.extend("publish", function () {
    gulp.task("publish_assets", function () {
        gulp.src("").pipe(shell([
            "C:\\xampp\\php\\php.exe C:\\Users\\Venko\\PhpstormProjects\\provision-cms-5.3\\artisan vendor:publish --tag=public --tag=views --force"
        ]));
    });
});

elixir(function (mix) {
    mix.combine([
        'Resources/Assets/js/init.js',
    ], 'Public/assets/js/all.js');


    mix.sass([
        '../bower_components/fileicon.css/fileicon.css',
        'style.scss'
    ], 'Public/assets/css/all.css', 'Resources/Assets/sass/');


    mix.publish();
});


gulp.task("full", ["all", "publish_assets"], function () {

});