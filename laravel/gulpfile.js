const elixir = require('laravel-elixir'),
    remove = require('laravel-elixir-remove'),
    cleanCSS = require('gulp-clean-css'),
    uglify = require('gulp-uglify'),
    gutil = require('gulp-util'),
    pump = require('pump'),
    paths = {
        'fontawesome': 'font-awesome/',
        'dataTables': 'datatables/media/',
        'bootstrapDialog': 'bootstrap3-dialog/dist/',
        'datetimepicker': 'eonasdan-bootstrap-datetimepicker/build/',
        'moment': 'moment/',
        'MDBPro': 'MDBPro/'
    };

var Task = elixir.Task;

elixir.config.sourcemaps = false;

elixir.extend('minCSS', function () {
    new Task('min_css', function () {
        return gulp.src('public/build/css/*.css')
            .pipe(cleanCSS())
            .pipe(gulp.dest('public/build/css'));
    });
});

elixir.extend('ugJS', function () {
    new Task('ug_js', function () {
        // return pump([
        //     gulp.src('public/build/js/*.js'),

        //     uglify({mangle: true}),
        //     gulp.dest('public/build/js')
        // ]);

        return gulp.src('public/build/js/*.js')
            .pipe(uglify().on('error', gutil.log))
            //.pipe(uglify({mangle: true}))
            .pipe(gulp.dest('public/build/js'));
    });
});

elixir(function (mix) {
    mix.remove('public/build')
    //.copy('node_modules/' + paths.fontawesome + 'fonts', 'public/fonts')
        .copy('bower_components/' + paths.fontawesome + 'fonts', 'public/build/fonts')
        .copy('resources/assets/js/bootstrap3-dialog/js', 'bower_components/bootstrap3-dialog/dist/js')
        .copy('resources/assets/sass/mymdb.scss', 'bower_components/MDBPro/sass')
        .copy('resources/assets/sass/mdb/pro/_red_skin.scss', 'bower_components/MDBPro/sass/mdb/pro')
        .sass('sass/mymdb.scss', 'bower_components/' + paths.MDBPro + 'mymdb', 'bower_components/' + paths.MDBPro) //src, output, baseDir,
        .styles([
            paths.fontawesome + 'css/font-awesome.css',
            paths.MDBPro + 'css/bootstrap.css',
            paths.MDBPro + 'mymdb/mymdb.css',
            paths.dataTables + 'css/dataTables.bootstrap4.css',
            paths.bootstrapDialog + 'css/bootstrap-dialog.css',
            paths.datetimepicker + 'css/bootstrap-datetimepicker.css'
        ], 'public/css/vendor.css', 'bower_components')
        .scripts([
            paths.MDBPro + 'js/jquery-3.1.1.js',
            paths.MDBPro + 'js/tether.js',
            paths.MDBPro + 'js/bootstrap.js',
            paths.MDBPro + 'js/mods/jquery-easing.js',
            paths.MDBPro + 'js/mods/global.js',
            paths.MDBPro + 'js/mods/velocity.min.js',
            paths.MDBPro + 'js/mods/scrolling-nav.js',
            paths.MDBPro + 'js/mods/waves.js',
            paths.MDBPro + 'js/mods/smooth-scroll.js',
            paths.MDBPro + 'js/mods/dropdown.js',
            paths.MDBPro + 'js/mods/buttons.js',
            paths.MDBPro + 'js/mods/hammer.js',
            paths.MDBPro + 'js/mods/jquery.hammer.js',
            paths.MDBPro + 'js/mods/sideNav.js',
            paths.MDBPro + 'js/mods/forms.js',
            paths.MDBPro + 'js/mods/scrollbar.js',
            paths.moment + 'js/moment.js',
            paths.datetimepicker + 'js/bootstrap-datetimepicker.js',
            paths.dataTables + 'js/jquery.dataTables.js',
            paths.dataTables + 'js/dataTables.bootstrap4.js',
            //paths.spin + 'spin.js',
            paths.bootstrapDialog + 'js/bootstrap-dialog4.js'
        ], 'public/js/vendor.js', 'bower_components')
    .version(['css', 'js'])
    .minCSS()
    .ugJS();
});